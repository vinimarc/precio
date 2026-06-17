#!/usr/bin/env python3
"""
scraper.py - Busca preços em múltiplos sites brasileiros (PARALELO + OTIMIZADO)
Uso: python scraper.py "nome do produto"
Busca em Pichau, Mercado Livre, Amazon, Kabum, B2Brazil em paralelo.
Retorna JSON consolidado com todos os resultados.
"""

import sys
import json
from functools import lru_cache
from concurrent.futures import ThreadPoolExecutor, as_completed
from urllib.parse import quote
import hashlib

try:
    import requests
    from bs4 import BeautifulSoup
except ImportError:
    requests = None
    BeautifulSoup = None

_session = None


def _get_session():
    global _session
    if _session is None and requests:
        _session = requests.Session()
        _session.headers.update({
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Accept-Encoding": "gzip, deflate",
            "Connection": "keep-alive",
            "Accept-Language": "pt-BR,pt;q=0.9",
        })
    return _session


def _normalizar_nome(nome: str) -> str:
    """Normaliza nome para deduplicação."""
    return nome.lower().strip()[:40]


def _hash_produto(nome: str, preco: float) -> str:
    """Cria hash para deduplicação de produtos."""
    chave = f"{_normalizar_nome(nome)}#{round(preco, 2)}"
    return hashlib.md5(chave.encode()).hexdigest()


def _buscar_pichau(query: str) -> list:
    """Busca na Pichau via GraphQL."""
    try:
        url = "https://pichau.com.br/graphql"
        graphql_query = (
            "query SearchProducts($search:String!,$pageSize:Int!)"
            "{products(search:$search,pageSize:$pageSize,sort:{relevance:DESC})"
            "{items{name sku url_key "
            "price_range{minimum_price{final_price{value}"
            "regular_price{value}discount{percent_off}}}"
            "small_image{url}stock_status}}}"
        )
        payload = json.dumps({
            "query": graphql_query,
            "variables": {"search": query, "pageSize": 50}
        })

        session = _get_session()
        if not session:
            return []

        response = session.post(
            url,
            data=payload,
            headers={
                "Content-Type": "application/json",
                "Origin": "https://www.pichau.com.br",
                "Referer": "https://www.pichau.com.br/",
            },
            timeout=6
        )

        data = response.json()
        items = data.get("data", {}).get("products", {}).get("items", [])

        produtos = []
        for item in items:
            preco_info = item.get("price_range", {}).get("minimum_price", {})
            preco_final = preco_info.get("final_price", {}).get("value", 0)
            preco_orig = preco_info.get("regular_price", {}).get("value")
            desconto = preco_info.get("discount", {}).get("percent_off")

            produtos.append({
                "nome": item.get("name", ""),
                "preco": preco_final,
                "preco_orig": preco_orig if preco_orig and preco_orig != preco_final else None,
                "desconto": round(desconto, 1) if desconto else None,
                "url": f"https://www.pichau.com.br/{item.get('url_key', '')}",
                "imagem": item.get("small_image", {}).get("url", ""),
                "em_estoque": item.get("stock_status") == "IN_STOCK",
                "site": "Pichau",
            })
        return produtos
    except Exception:
        return []


def _buscar_mercado_livre(query: str) -> list:
    """Busca no Mercado Livre via web scraping."""
    if not (requests and BeautifulSoup):
        return []

    try:
        url = f"https://lista.mercadolivre.com.br/{quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"data-item-id": True})[:50]:
            try:
                nome_elem = item.find("h2")
                preco_elem = item.find("span", {"class": "price-tag"})
                link_elem = item.find("a", {"class": "poly-component__title"})

                if not (nome_elem and preco_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco_text = preco_elem.get_text(strip=True)
                preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": link_elem.get("href", ""),
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Mercado Livre",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_amazon(query: str) -> list:
    """Busca na Amazon.com.br."""
    if not requests:
        return []

    try:
        url = f"https://www.amazon.com.br/s?k={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"data-component-type": "s-search-result"})[:50]:
            try:
                nome_elem = item.find("h2")
                preco_elem = item.find("span", {"class": "a-price-whole"})
                link_elem = item.find("a", {"class": "a-link-normal"})

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": f"https://www.amazon.com.br{link_elem.get('href', '')}",
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Amazon",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_kabum(query: str) -> list:
    """Busca na Kabum."""
    if not requests:
        return []

    try:
        url = f"https://www.kabum.com.br/busca/{quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("article", {"class": "productCard"})[:50]:
            try:
                nome_elem = item.find("span", {"class": "nameProduct"})
                preco_elem = item.find("span", {"class": "priceProduct"})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100] if nome_elem else ""
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": link_elem.get("href", ""),
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Kabum",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_b2brazil(query: str) -> list:
    """Busca na B2Brazil."""
    if not requests:
        return []

    try:
        url = f"https://www.b2brazil.com.br/busca?q={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": "product-item"})[:50]:
            try:
                nome_elem = item.find("h2")
                preco_elem = item.find("span", {"class": "price"})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": link_elem.get("href", ""),
                    "imagem": "",
                    "em_estoque": True,
                    "site": "B2Brazil",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_magazine_luiza(query: str) -> list:
    """Busca na Magazine Luiza."""
    if not requests:
        return []

    try:
        url = f"https://www.magazineluiza.com.br/busca/{quote(query)}/"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": "product-item"})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("a", {"class": "product-title"})
                preco_elem = item.find("span", {"class": ["price", "best-price"]})
                link_elem = item.find("a", {"class": "product-link"}) or item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.magazineluiza.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Magazine Luiza",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_americanas(query: str) -> list:
    """Busca na Americanas."""
    if not requests:
        return []

    try:
        url = f"https://www.americanas.com.br/busca/{quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"data-testid": "product-item"})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("span", {"class": "productTitle"})
                preco_elem = item.find("span", {"class": ["price", "priceText"]})
                link_elem = item.find("a", {"href": True})

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.americanas.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Americanas",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_netshoes(query: str) -> list:
    """Busca na Netshoes."""
    if not requests:
        return []

    try:
        url = f"https://www.netshoes.com.br/busca?q={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": "product"})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("a", {"class": "product-name"})
                preco_elem = item.find("span", {"class": ["price", "preco"]})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.netshoes.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Netshoes",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_carrefour(query: str) -> list:
    """Busca na Carrefour."""
    if not requests:
        return []

    try:
        url = f"https://www.carrefour.com.br/busca?q={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": ["product", "productCard"]})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("span", {"class": "productTitle"})
                preco_elem = item.find("span", {"class": ["price", "priceText"]})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.carrefour.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Carrefour",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_walmart(query: str) -> list:
    """Busca na Walmart."""
    if not requests:
        return []

    try:
        url = f"https://www.walmart.com.br/busca?q={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": ["product", "productCard"]})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("a", {"class": "product-name"})
                preco_elem = item.find("span", {"class": ["price", "priceText"]})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.walmart.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Walmart",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_olx(query: str) -> list:
    """Busca na OLX."""
    if not requests:
        return []

    try:
        url = f"https://www.olx.com.br/brasil?q={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": "ad"})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("a", {"class": "ad-title"})
                preco_elem = item.find("span", {"class": ["price", "preco"]})
                link_elem = item.find("a", {"class": "ad-link"}) or item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.olx.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "OLX",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_shopee(query: str) -> list:
    """Busca na Shopee (com limitações de JavaScript)."""
    if not requests:
        return []

    try:
        url = f"https://shopee.com.br/search?keyword={quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": ["product", "shopee-product"]})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("span", {"class": "product-name"})
                preco_elem = item.find("span", {"class": ["price", "shopee-price"]})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://shopee.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Shopee",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def _buscar_shein(query: str) -> list:
    """Busca na Shein (com limitações de JavaScript)."""
    if not requests:
        return []

    try:
        url = f"https://www.shein.com.br/search/{quote(query)}"
        session = _get_session()
        response = session.get(url, timeout=6)

        if response.status_code != 200:
            return []

        if not BeautifulSoup:
            return []

        soup = BeautifulSoup(response.content, "html.parser")
        produtos = []

        for item in soup.find_all("div", {"class": ["product", "shein-product"]})[:50]:
            try:
                nome_elem = item.find("h2") or item.find("span", {"class": "product-title"})
                preco_elem = item.find("span", {"class": ["price", "priceText"]})
                link_elem = item.find("a")

                if not (nome_elem and link_elem):
                    continue

                nome = nome_elem.get_text(strip=True)[:100]
                preco = 0
                if preco_elem:
                    preco_text = preco_elem.get_text(strip=True)
                    preco = float(preco_text.replace("R$", "").replace(".", "").replace(",", ".").strip()) if preco_text else 0

                url_produto = link_elem.get("href", "")
                if url_produto and not url_produto.startswith("http"):
                    url_produto = f"https://www.shein.com.br{url_produto}"

                produtos.append({
                    "nome": nome,
                    "preco": preco,
                    "preco_orig": None,
                    "desconto": None,
                    "url": url_produto,
                    "imagem": "",
                    "em_estoque": True,
                    "site": "Shein",
                })
            except Exception:
                continue

        return produtos
    except Exception:
        return []


def buscar_todos_sites(query: str, pagina: int = 1, ordem: str = "preco_asc", filtro_sites: str = "") -> str:
    """
    Busca em TODOS os sites em paralelo com suporte a paginação, ordenação e filtro.
    - pagina: número da página (1+)
    - ordem: "preco_asc", "preco_desc", "relevancia"
    - filtro_sites: lista de sites separada por vírgula (ex: "Pichau,Amazon")
    Retorna JSON com 7 itens por página + metadados.
    """
    if not query or len(query.strip()) < 2:
        return json.dumps({"erro": "Pesquisa muito curta (min. 2 caracteres)"})

    pagina = max(1, int(pagina) if isinstance(pagina, (int, str)) else 1)

    buscadores = [
        _buscar_pichau,
        _buscar_mercado_livre,
        _buscar_amazon,
        _buscar_kabum,
        _buscar_b2brazil,
        _buscar_magazine_luiza,
        _buscar_americanas,
        _buscar_netshoes,
        _buscar_carrefour,
        _buscar_walmart,
        _buscar_olx,
        _buscar_shopee,
        _buscar_shein,
    ]

    todos_produtos = []
    vistos = set()

    with ThreadPoolExecutor(max_workers=13) as executor:
        futures = {executor.submit(buscador, query): buscador.__name__ for buscador in buscadores}

        for future in as_completed(futures, timeout=20):
            try:
                resultados = future.result()
                for produto in resultados:
                    hash_prod = _hash_produto(produto["nome"], produto["preco"])
                    if hash_prod not in vistos:
                        vistos.add(hash_prod)
                        todos_produtos.append(produto)
            except Exception:
                continue

    filtro_lista = [s.strip() for s in filtro_sites.split(",")] if filtro_sites.strip() else []
    if filtro_lista:
        todos_produtos = [p for p in todos_produtos if p["site"] in filtro_lista]

    if ordem == "preco_desc":
        todos_produtos.sort(key=lambda x: x["preco"], reverse=True)
    elif ordem == "preco_asc":
        todos_produtos.sort(key=lambda x: x["preco"])
    else:
        todos_produtos.sort(key=lambda x: x["preco"])

    itens_por_pagina = 7
    total_itens = len(todos_produtos)
    total_paginas = (total_itens + itens_por_pagina - 1) // itens_por_pagina
    pagina = min(pagina, max(1, total_paginas))

    inicio = (pagina - 1) * itens_por_pagina
    fim = inicio + itens_por_pagina
    produtos_pagina = todos_produtos[inicio:fim]

    sites_disponiveis = sorted(set(p["site"] for p in todos_produtos))

    return json.dumps({
        "total": total_itens,
        "pagina_atual": pagina,
        "total_paginas": total_paginas,
        "itens_por_pagina": itens_por_pagina,
        "sites_disponiveis": sites_disponiveis,
        "sites_buscados": len(buscadores),
        "filtro_ativo": filtro_sites if filtro_sites else None,
        "ordem_ativa": ordem,
        "produtos": produtos_pagina,
    }, ensure_ascii=False)


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"erro": "Uso: python scraper.py \"termo\" [pagina] [ordem] [filtro_sites]"}))
        sys.exit(1)

    termo = sys.argv[1].strip()
    pagina = int(sys.argv[2]) if len(sys.argv) > 2 else 1
    ordem = sys.argv[3] if len(sys.argv) > 3 else "preco_asc"
    filtro = sys.argv[4] if len(sys.argv) > 4 else ""

    if len(termo) < 2:
        print(json.dumps({"erro": "Pesquisa muito curta (min. 2 caracteres)"}))
        sys.exit(1)

    print(buscar_todos_sites(termo, pagina, ordem, filtro))