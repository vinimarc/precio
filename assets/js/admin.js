(function () {
    'use strict';

    const shell = document.querySelector('.admin-shell');
    const root = document.documentElement;
    const data = window.PRECIO_ADMIN_DATA || {};
    const charts = [];

    function cssVar(name) {
        return getComputedStyle(root).getPropertyValue(name).trim();
    }

    function showToast(message) {
        const toastEl = document.getElementById('adminToast');
        if (!toastEl || !window.bootstrap) return;
        toastEl.querySelector('.toast-body').textContent = message || 'Ação concluída.';
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2800 }).show();
    }

    function setSection(sectionId) {
        const fallback = document.getElementById(sectionId) ? sectionId : 'dashboard';

        document.querySelectorAll('[data-section]').forEach((section) => {
            section.classList.toggle('active', section.id === fallback);
        });

        document.querySelectorAll('[data-section-link]').forEach((link) => {
            link.classList.toggle('active', link.dataset.sectionLink === fallback);
        });

        if (history.replaceState) {
            history.replaceState(null, '', '#' + fallback);
        }

        shell?.classList.remove('sidebar-open');
        requestAnimationFrame(() => charts.forEach((chart) => chart.resize()));
    }

    function initNavigation() {
        document.querySelectorAll('[data-section-link]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                setSection(link.dataset.sectionLink);
            });
        });

        const initial = window.location.hash ? window.location.hash.slice(1) : 'dashboard';
        setSection(initial);
    }

    function initSidebar() {
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 991px)').matches) {
                shell?.classList.toggle('sidebar-open');
                return;
            }

            const current = shell?.dataset.sidebar || 'expanded';
            shell.dataset.sidebar = current === 'expanded' ? 'collapsed' : 'expanded';
        });

        document.querySelectorAll('[data-sidebar-close]').forEach((element) => {
            element.addEventListener('click', () => shell?.classList.remove('sidebar-open'));
        });
    }

    function initTheme() {
        const storedTheme = localStorage.getItem('precio-admin-theme');
        const preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = storedTheme || preferredTheme;

        root.dataset.theme = theme;
        updateThemeButton(theme);

        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = nextTheme;
            localStorage.setItem('precio-admin-theme', nextTheme);
            updateThemeButton(nextTheme);
            rebuildCharts();
        });
    }

    function updateThemeButton(theme) {
        const icon = document.querySelector('#themeToggle i');
        if (!icon) return;
        icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    }

    function chartColors() {
        return {
            text: cssVar('--admin-muted'),
            grid: cssVar('--admin-border'),
            primary: cssVar('--admin-primary'),
            success: cssVar('--admin-success'),
            warning: cssVar('--admin-warning'),
            danger: cssVar('--admin-danger'),
            surface: cssVar('--admin-surface')
        };
    }

    function baseChartOptions(extra) {
        const colors = chartColors();
        return Object.assign({
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: colors.text, boxWidth: 12, usePointStyle: true } },
                tooltip: { backgroundColor: '#0f172a', padding: 12, titleFont: { weight: '700' } }
            },
            scales: {
                x: { ticks: { color: colors.text }, grid: { color: colors.grid } },
                y: { ticks: { color: colors.text }, grid: { color: colors.grid }, beginAtZero: true }
            }
        }, extra || {});
    }

    function createChart(id, configFactory) {
        const canvas = document.getElementById(id);
        if (!canvas || !window.Chart) return;
        const chart = new Chart(canvas, configFactory());
        charts.push(chart);
    }

    function initCharts() {
        const colors = chartColors();

        createChart('searchesLineChart', () => ({
            type: 'line',
            data: {
                labels: data.searchesByDay?.labels || [],
                datasets: [{
                    label: 'Pesquisas',
                    data: data.searchesByDay?.values || [],
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(37, 99, 235, .12)',
                    borderWidth: 3,
                    fill: true,
                    tension: .38,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: baseChartOptions()
        }));

        createChart('usersBarChart', () => ({
            type: 'bar',
            data: {
                labels: data.userGrowth?.labels || [],
                datasets: [{
                    label: 'Usuários',
                    data: data.userGrowth?.values || [],
                    backgroundColor: colors.primary,
                    borderRadius: 7,
                    maxBarThickness: 44
                }]
            },
            options: baseChartOptions({ plugins: { legend: { display: false } } })
        }));

        createChart('productsDoughnutChart', () => doughnutConfig(data.topProducts));
        createChart('analyticsProductsChart', () => doughnutConfig(data.topProducts));
        createChart('analyticsStoresChart', () => ({
            type: 'bar',
            data: {
                labels: data.storesAccess?.labels || [],
                datasets: [{ label: 'Acessos', data: data.storesAccess?.values || [], backgroundColor: colors.success, borderRadius: 7 }]
            },
            options: baseChartOptions({ indexAxis: 'y', plugins: { legend: { display: false } } })
        }));
        createChart('noResultChart', () => ({
            type: 'line',
            data: {
                labels: data.noResult?.labels || [],
                datasets: [{ label: 'Sem resultado', data: data.noResult?.values || [], borderColor: colors.warning, backgroundColor: 'rgba(217, 119, 6, .12)', fill: true, tension: .35 }]
            },
            options: baseChartOptions()
        }));
        createChart('storeClicksChart', () => ({
            type: 'bar',
            data: {
                labels: data.storeClicks?.labels || [],
                datasets: [{ label: 'Cliques', data: data.storeClicks?.values || [], backgroundColor: colors.primary, borderRadius: 7 }]
            },
            options: baseChartOptions({ plugins: { legend: { display: false } } })
        }));
    }

    function doughnutConfig(source) {
        const colors = chartColors();
        return {
            type: 'doughnut',
            data: {
                labels: source?.labels || [],
                datasets: [{
                    data: source?.values || [],
                    backgroundColor: [colors.primary, colors.success, colors.warning, '#14b8a6', colors.danger],
                    borderColor: colors.surface,
                    borderWidth: 4
                }]
            },
            options: baseChartOptions({
                cutout: '68%',
                scales: {},
                plugins: { legend: { position: 'bottom', labels: { color: colors.text, boxWidth: 10, usePointStyle: true } } }
            })
        };
    }

    function rebuildCharts() {
        while (charts.length) {
            charts.pop().destroy();
        }
        initCharts();
    }

    function initTables() {
        document.querySelectorAll('.sortable-table').forEach((table) => {
            const state = { page: 1, query: '', filter: '', sortIndex: -1, sortDirection: 'asc' };

            table.querySelectorAll('th[data-sort]').forEach((header) => {
                header.addEventListener('click', () => {
                    const columnIndex = header.cellIndex;
                    state.sortDirection = state.sortIndex === columnIndex && state.sortDirection === 'asc' ? 'desc' : 'asc';
                    state.sortIndex = columnIndex;
                    table.querySelectorAll('th').forEach((th) => th.classList.remove('sorted-asc', 'sorted-desc'));
                    header.classList.add(state.sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
                    renderTable(table, state);
                });
            });

            document.querySelectorAll(`[data-table-search="${table.id}"]`).forEach((input) => {
                input.addEventListener('input', () => {
                    state.query = input.value.trim().toLowerCase();
                    state.page = 1;
                    renderTable(table, state);
                });
            });

            document.querySelectorAll(`[data-table-filter="${table.id}"]`).forEach((select) => {
                select.addEventListener('change', () => {
                    state.filter = select.value;
                    state.page = 1;
                    renderTable(table, state);
                });
            });

            renderTable(table, state);
        });
    }

    function renderTable(table, state) {
        const pageSize = Number(table.dataset.pageSize || 6);
        let rows = Array.from(table.tBodies[0].rows);

        rows.forEach((row) => row.hidden = true);

        rows = rows.filter((row) => {
            const textMatch = !state.query || row.textContent.toLowerCase().includes(state.query);
            const resultCount = Number(row.dataset.results || 0);
            const filterMatch = !state.filter || (state.filter === 'high' ? resultCount > 100 : resultCount <= 100);
            return textMatch && filterMatch;
        });

        if (state.sortIndex >= 0) {
            rows.sort((a, b) => {
                const aText = a.cells[state.sortIndex]?.textContent.trim() || '';
                const bText = b.cells[state.sortIndex]?.textContent.trim() || '';
                const aNumber = parseFloat(aText.replace(/[^\d,-]/g, '').replace(',', '.'));
                const bNumber = parseFloat(bText.replace(/[^\d,-]/g, '').replace(',', '.'));
                const comparison = !Number.isNaN(aNumber) && !Number.isNaN(bNumber)
                    ? aNumber - bNumber
                    : aText.localeCompare(bText, 'pt-BR', { numeric: true });
                return state.sortDirection === 'asc' ? comparison : -comparison;
            });
        }

        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        state.page = Math.min(state.page, totalPages);
        const start = (state.page - 1) * pageSize;

        rows.slice(start, start + pageSize).forEach((row) => row.hidden = false);
        renderPagination(table.id, state, totalPages, rows.length, () => renderTable(table, state));
    }

    function renderPagination(tableId, state, totalPages, totalRows, rerender) {
        const footer = document.querySelector(`[data-pagination-for="${tableId}"]`);
        if (!footer) return;

        footer.innerHTML = '';
        const summary = document.createElement('span');
        summary.className = 'me-auto text-muted small align-self-center';
        summary.textContent = `${totalRows} registros`;
        footer.appendChild(summary);

        for (let page = 1; page <= totalPages; page += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `btn btn-sm ${page === state.page ? 'btn-primary' : 'btn-soft'}`;
            button.textContent = page;
            button.addEventListener('click', () => {
                state.page = page;
                rerender();
            });
            footer.appendChild(button);
        }
    }

    function initLogs() {
        document.querySelectorAll('[data-log-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const level = button.dataset.logFilter;
                document.querySelectorAll('[data-log-filter]').forEach((item) => {
                    item.classList.toggle('btn-primary', item === button);
                    item.classList.toggle('btn-soft', item !== button);
                });
                document.querySelectorAll('#logList .log-row').forEach((row) => {
                    row.hidden = level !== 'ALL' && row.dataset.level !== level;
                });
            });
        });
    }

    function initNotifications() {
        const list = document.getElementById('notificationList');
        const count = document.getElementById('notificationCount');
        const empty = document.getElementById('notificationEmpty');
        const clearButton = document.querySelector('[data-clear-notifications]');
        if (!list || !count || !empty) return;

        const storageKey = 'precio-admin-read-notifications';
        const readIds = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]'));

        function persist() {
            localStorage.setItem(storageKey, JSON.stringify(Array.from(readIds)));
        }

        function refresh() {
            const items = Array.from(list.querySelectorAll('[data-notification-id]'));
            let visible = 0;

            items.forEach((item) => {
                const isRead = readIds.has(item.dataset.notificationId);
                item.hidden = isRead;
                if (!isRead) visible += 1;
            });

            count.textContent = visible;
            count.classList.toggle('is-empty', visible === 0);
            empty.hidden = visible !== 0;
            if (clearButton) clearButton.disabled = visible === 0;
        }

        list.addEventListener('click', (event) => {
            const item = event.target.closest('[data-notification-id]');
            if (!item) return;
            readIds.add(item.dataset.notificationId);
            persist();
            refresh();
            showToast('Notificação marcada como lida.');
        });

        clearButton?.addEventListener('click', () => {
            list.querySelectorAll('[data-notification-id]').forEach((item) => readIds.add(item.dataset.notificationId));
            persist();
            refresh();
            showToast('Notificações limpas.');
        });

        refresh();
    }

    function initGlobalSearch() {
        const input = document.getElementById('globalSearch');
        if (!input) return;

        document.addEventListener('keydown', (event) => {
            if (event.key === '/' && document.activeElement !== input) {
                event.preventDefault();
                input.focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            const query = input.value.trim().toLowerCase();
            if (!query) return;

            const target = Array.from(document.querySelectorAll('[data-section]')).find((section) => {
                return section.textContent.toLowerCase().includes(query);
            });

            if (target) {
                setSection(target.id);
                showToast(`Resultado encontrado em ${target.querySelector('h1')?.textContent || 'seção'}.`);
            } else {
                showToast('Nenhum resultado encontrado no painel.');
            }
        });
    }

    function initToasts() {
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-toast]');
            if (trigger) showToast(trigger.dataset.toast);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTheme();
        initSidebar();
        initNavigation();
        initCharts();
        initTables();
        initLogs();
        initNotifications();
        initGlobalSearch();
        initToasts();
    });
})();
