# 🛍️ Scraper de Preços - Documentação Completa

## 📊 Sites Integrados (13 Total)

### ✅ **TOTALMENTE FUNCIONAL** (Scraping HTML Direto)
1. **Pichau** - API GraphQL (MAIS RÁPIDO)
2. **Mercado Livre** - HTML parsing
3. **Magazine Luiza** - HTML parsing
4. **Americanas** - HTML parsing
5. **Kabum** - HTML parsing
6. **B2Brazil** - HTML parsing
7. **Netshoes** - HTML parsing
8. **Carrefour** - HTML parsing
9. **Walmart** - HTML parsing
10. **OLX** - HTML parsing

### ⚠️ **COM LIMITAÇÕES** (JavaScript Pesado)
11. **Shopee** - Requer melhorias (JavaScript)
12. **Shein** - Requer melhorias (JavaScript)
13. **Amazon** - Requer melhorias (Cloudflare)

---

## 🚀 Instalação & Uso

### Dependências Obrigatórias
```bash
pip install requests beautifulsoup4
```

### Uso Básico
```bash
python scraper.py "RTX 4060"
```

### Resultado
```json
{
  "total": 450,
  "sites_buscados": 13,
  "produtos": [
    {
      "nome": "RTX 4060 8GB",
      "preco": 1299.99,
      "preco_orig": 1599.99,
      "desconto": 18.7,
      "url": "https://...",
      "imagem": "https://...",
      "em_estoque": true,
      "site": "Pichau"
    }
  ]
}
```

---

## ⚡ Performance

| Métrica | Valor |
|---------|-------|
| Timeout por Site | 6 segundos |
| Timeout Total | 20 segundos |
| Threads Paralelos | 13 simultâneos |
| Max Produtos por Site | 50 |
| **Total Max Produtos** | **650** (com dedup) |
| Cache | 256 últimas buscas |

---

## 🔧 Para Melhorar Shopee/Shein (Opcional)

Se quiser suporte MELHOR para sites com JavaScript pesado, instale:

```bash
pip install selenium playwright
# Ou um único:
pip install playwright
playwright install chromium
```

Isso permitirá buscar em Shopee, Shein e Amazon de forma mais confiável, mas tornará as buscas **10-15% mais lentas**.

---

## 📋 Estrutura da Resposta

```json
{
  "total": int,              // Total de produtos únicos encontrados
  "sites_buscados": int,     // Número de sites que retornaram resultados
  "produtos": [
    {
      "nome": string,        // Nome do produto
      "preco": float,        // Preço final
      "preco_orig": float|null,  // Preço original (se houver desconto)
      "desconto": float|null,    // % de desconto
      "url": string,         // Link completo
      "imagem": string,      // URL da imagem
      "em_estoque": boolean, // Disponibilidade
      "site": string         // Nome do e-commerce
    }
  ]
}
```

---

## 🎯 Boas Práticas

✅ **FAÇA:**
- Usar termos de busca com 2+ caracteres
- Cachear resultados (LRU automático em 256 buscas)
- Fazer requisições com pequenos intervalos (~5s)
- Usar User-Agent realista (já configurado)

❌ **NÃO FAÇA:**
- Requisições em loop rápido (< 1s)
- Ignorar erros de conexão
- Modificar User-Agents para evitar detecção
- Armazenar grande volume de IPs para ataque distribuído

---

## 🐛 Troubleshooting

### "Erro na requisição"
- Verifique conexão de internet
- Confirme que o site não está bloqueando
- Aumente timeout em `scraper.py`

### Poucos resultados de um site
- Alguns sites têm proteção anti-bot
- Tente novamente com outro termo
- Aguarde alguns segundos entre buscas

### Shopee/Shein retornando vazio
- Esses sites usam JavaScript pesado
- Solução: Instale `selenium` ou `playwright` (opcional)
- A busca funciona mas pode ser incompleta

---

## 📈 Próximas Melhorias Possíveis

- [ ] Suporte a Selenium para JavaScript (Shopee, Shein)
- [ ] Busca por faixa de preço
- [ ] Histórico de preços
- [ ] Alertas de preço baixo
- [ ] Integração com banco de dados
- [ ] API REST

---

**Última atualização:** 2026-06-16
**Versão:** 2.0 - Multi-site Paralelo
