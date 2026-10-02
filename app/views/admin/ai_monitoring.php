<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="activity" class="w-7 h-7 text-violet-600"></i>
                Monitoring Penggunaan AI
            </h2>
            <p class="text-gray-600 mt-1">Statistik penggunaan asisten AI "Pak Admin" — token, biaya, topik, dan aktivitas user</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="loadMonitoring()" id="btn-refresh"
                class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 flex items-center gap-2 transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" id="filter-start"
                    class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" id="filter-end"
                    class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            </div>
            <div class="flex gap-2">
                <button onclick="setPeriod(7)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700">7 Hari</button>
                <button onclick="setPeriod(30)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700">30 Hari</button>
                <button onclick="setPeriod(90)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700">90 Hari</button>
                <button onclick="setPeriod(0)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700">Hari Ini</button>
            </div>
            <button onclick="loadMonitoring()"
                class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
            </button>
        </div>
    </div>

    <!-- Loading -->
    <div id="monitoring-loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
        <span class="ml-3 text-gray-500">Memuat data...</span>
    </div>

    <!-- Content -->
    <div id="monitoring-content" class="hidden space-y-6">

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="message-square" class="w-4 h-4 text-violet-500"></i> Total Request
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-requests">0</p>
                <p class="text-xs text-gray-400 mt-1"><span id="stat-chat">0</span> chat · <span id="stat-title">0</span> judul</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i> Total Token
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-tokens">0</p>
                <p class="text-xs text-gray-400 mt-1"><span id="stat-prompt">0</span> in · <span id="stat-completion">0</span> out</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="users" class="w-4 h-4 text-blue-500"></i> User Aktif
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-users">0</p>
                <p class="text-xs text-gray-400 mt-1"><span id="stat-conversations">0</span> percakapan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="timer" class="w-4 h-4 text-emerald-500"></i> Rata-rata Latency
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-latency">0 ms</p>
                <p class="text-xs text-gray-400 mt-1">waktu respons AI</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i> Error
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-errors">0</p>
                <p class="text-xs text-gray-400 mt-1"><span id="stat-error-rate">0</span>% error rate</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i data-lucide="wallet" class="w-4 h-4 text-green-500"></i> Estimasi Biaya
                </div>
                <p class="text-2xl font-bold text-gray-800" id="stat-cost">Rp 0</p>
                <p class="text-xs text-gray-400 mt-1" id="stat-cost-note">harga belum diatur</p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-violet-500"></i> Tren Harian
                </h3>
                <div class="h-72"><canvas id="chart-daily"></canvas></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-blue-500"></i> Distribusi Model
                </h3>
                <div class="h-72 relative"><canvas id="chart-model"></canvas><p id="empty-model" class="hidden absolute inset-0 flex items-center justify-center text-gray-400 text-sm">Belum ada data</p></div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i> Aktivitas per Jam
                </h3>
                <div class="h-64"><canvas id="chart-hourly"></canvas></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="gauge" class="w-5 h-5 text-emerald-500"></i> Distribusi Latency
                </h3>
                <div class="h-64 relative"><canvas id="chart-latency"></canvas><p id="empty-latency" class="hidden absolute inset-0 flex items-center justify-center text-gray-400 text-sm">Belum ada data</p></div>
            </div>
        </div>

        <!-- Topik Populer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="hash" class="w-5 h-5 text-pink-500"></i> Topik yang Sering Dibahas
            </h3>
            <div id="topics-container" class="flex flex-wrap gap-2"></div>
        </div>

        <!-- Top Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-blue-500"></i> Penggunaan per User
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-right">Request</th>
                            <th class="px-4 py-3 text-right">Token</th>
                            <th class="px-4 py-3 text-right">Percakapan</th>
                            <th class="px-4 py-3 text-right">Avg Latency</th>
                        </tr>
                    </thead>
                    <tbody id="users-table" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <!-- Recent Conversations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="messages-square" class="w-5 h-5 text-violet-500"></i> Percakapan Terbaru
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-right">Request</th>
                            <th class="px-4 py-3 text-right">Token</th>
                            <th class="px-4 py-3 text-right">Avg Latency</th>
                            <th class="px-4 py-3 text-left">Aktivitas Terakhir</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="conversations-table" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <!-- Recent Errors -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="alert-octagon" class="w-5 h-5 text-red-500"></i> Error Terbaru
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Pesan Error</th>
                        </tr>
                    </thead>
                    <tbody id="errors-table" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal Detail Percakapan -->
<div id="modal-conversation" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-5 h-5 text-violet-500"></i>
                <span id="modal-title">Detail Percakapan</span>
            </h3>
            <button onclick="closeModal()" class="p-1 hover:bg-gray-100 rounded-lg">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>
        <div id="modal-body" class="p-5 overflow-y-auto flex-1 space-y-3"></div>
    </div>
</div>

<script>
    let charts = {};

    // Set default period (30 hari)
    document.addEventListener('DOMContentLoaded', function () {
        setPeriod(30);
        loadMonitoring();
    });

    function setPeriod(days) {
        const end = new Date();
        const start = new Date();
        if (days === 0) {
            // Hari ini
        } else {
            start.setDate(end.getDate() - days + 1);
        }
        document.getElementById('filter-start').value = formatDate(start);
        document.getElementById('filter-end').value = formatDate(end);
    }

    function formatDate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function fmtNum(n) {
        n = Number(n) || 0;
        return n.toLocaleString('id-ID');
    }

    function fmtRupiah(n) {
        n = Number(n) || 0;
        return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function fmtDate(s) {
        if (!s) return '-';
        const d = new Date(s.replace(' ', 'T'));
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) +
               ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    async function loadMonitoring() {
        const start = document.getElementById('filter-start').value;
        const end = document.getElementById('filter-end').value;

        document.getElementById('monitoring-loading').classList.remove('hidden');
        document.getElementById('monitoring-content').classList.add('hidden');

        try {
            const res = await fetch(`<?= BASEURL; ?>/admin/aiMonitoringData?start=${start}&end=${end}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Gagal memuat data');

            renderSummary(data.summary);
            renderDailyChart(data.daily_trend);
            renderModelChart(data.model_breakdown);
            renderHourlyChart(data.hourly_trend);
            renderLatencyChart(data.latency_stats);
            renderTopics(data.top_keywords);
            renderUsers(data.top_users);
            renderConversations(data.conversations);
            renderErrors(data.recent_errors);

            document.getElementById('monitoring-loading').classList.add('hidden');
            document.getElementById('monitoring-content').classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            document.getElementById('monitoring-loading').innerHTML =
                '<div class="text-red-600 flex items-center gap-2"><i data-lucide="alert-circle" class="w-5 h-5"></i> Error: ' + e.message + '</div>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function renderSummary(s) {
        document.getElementById('stat-requests').textContent = fmtNum(s.total_requests);
        document.getElementById('stat-chat').textContent = fmtNum(s.chat_requests);
        document.getElementById('stat-title').textContent = fmtNum(s.title_requests);
        document.getElementById('stat-tokens').textContent = fmtNum(s.total_tokens);
        document.getElementById('stat-prompt').textContent = fmtNum(s.total_prompt_tokens);
        document.getElementById('stat-completion').textContent = fmtNum(s.total_completion_tokens);
        document.getElementById('stat-users').textContent = fmtNum(s.unique_users);
        document.getElementById('stat-conversations').textContent = fmtNum(s.unique_conversations);
        document.getElementById('stat-latency').textContent = fmtNum(s.avg_latency_ms) + ' ms';
        document.getElementById('stat-errors').textContent = fmtNum(s.error_count);
        const rate = s.total_requests > 0 ? ((s.error_count / s.total_requests) * 100).toFixed(1) : '0';
        document.getElementById('stat-error-rate').textContent = rate;
        document.getElementById('stat-cost').textContent = fmtRupiah(s.estimated_cost);
        document.getElementById('stat-cost-note').textContent =
            (s.price_input_per_1k > 0 || s.price_output_per_1k > 0)
                ? `in ${fmtRupiah(s.price_input_per_1k)}/1K · out ${fmtRupiah(s.price_output_per_1k)}/1K`
                : 'harga belum diatur';
    }

    function destroyChart(key) {
        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
    }

    function renderDailyChart(trend) {
        destroyChart('daily');
        const labels = trend.map(t => t.tanggal);
        const ctx = document.getElementById('chart-daily');
        charts.daily = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Request',
                        data: trend.map(t => Number(t.requests)),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true, tension: 0.3, yAxisID: 'y',
                    },
                    {
                        label: 'Token',
                        data: trend.map(t => Number(t.tokens)),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        fill: false, tension: 0.3, yAxisID: 'y1',
                    },
                    {
                        label: 'User Aktif',
                        data: trend.map(t => Number(t.users)),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: false, tension: 0.3, yAxisID: 'y',
                    },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Request / User' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Token' } },
                }
            }
        });
    }

    function renderModelChart(models) {
        destroyChart('model');
        const ctx = document.getElementById('chart-model');
        const empty = document.getElementById('empty-model');
        if (!models.length) {
            ctx.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }
        ctx.classList.remove('hidden');
        empty.classList.add('hidden');
        charts.model = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: models.map(m => m.model),
                datasets: [{
                    data: models.map(m => Number(m.requests)),
                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#14b8a6'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function renderHourlyChart(hourly) {
        destroyChart('hourly');
        const labels = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0') + ':00');
        const map = {};
        hourly.forEach(h => map[Number(h.jam)] = Number(h.requests));
        const data = labels.map((_, i) => map[i] || 0);
        charts.hourly = new Chart(document.getElementById('chart-hourly'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Request', data, backgroundColor: '#f59e0b', borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderLatencyChart(stats) {
        destroyChart('latency');
        const ctx = document.getElementById('chart-latency');
        const empty = document.getElementById('empty-latency');
        if (!stats || stats.under_1s === undefined) {
            ctx.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }
        ctx.classList.remove('hidden');
        empty.classList.add('hidden');
        charts.latency = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['< 1s', '1-3s', '3-5s', '> 5s'],
                datasets: [{
                    label: 'Jumlah',
                    data: [Number(stats.under_1s), Number(stats.between_1_3s), Number(stats.between_3_5s), Number(stats.over_5s)],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderTopics(topics) {
        const container = document.getElementById('topics-container');
        if (!topics.length) {
            container.innerHTML = '<p class="text-gray-400 text-sm">Belum ada topik</p>';
            return;
        }
        const colors = ['bg-violet-100 text-violet-700', 'bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700',
                        'bg-amber-100 text-amber-700', 'bg-pink-100 text-pink-700', 'bg-teal-100 text-teal-700'];
        container.innerHTML = topics.map((t, i) => {
            const c = colors[i % colors.length];
            return `<button onclick="openConversation(${t.id_conversation})"
                class="${c} px-3 py-1.5 rounded-full text-sm font-medium hover:opacity-80 transition-opacity flex items-center gap-1.5">
                <span>${escapeHtml(t.judul)}</span>
                <span class="bg-white/60 px-1.5 rounded-full text-xs">${t.usage_count}</span>
            </button>`;
        }).join('');
    }

    function renderUsers(users) {
        const tbody = document.getElementById('users-table');
        if (!users.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada data</td></tr>';
            return;
        }
        tbody.innerHTML = users.map((u, i) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400">${i + 1}</td>
                <td class="px-4 py-3 font-medium text-gray-800">${escapeHtml(u.nama)}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">${escapeHtml(u.role)}</span></td>
                <td class="px-4 py-3 text-right">${fmtNum(u.total_requests)}</td>
                <td class="px-4 py-3 text-right font-medium">${fmtNum(u.total_tokens)}</td>
                <td class="px-4 py-3 text-right">${fmtNum(u.conversations)}</td>
                <td class="px-4 py-3 text-right text-gray-500">${fmtNum(u.avg_latency)} ms</td>
            </tr>
        `).join('');
    }

    function renderConversations(convs) {
        const tbody = document.getElementById('conversations-table');
        if (!convs.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada data</td></tr>';
            return;
        }
        tbody.innerHTML = convs.map(c => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800 max-w-xs truncate">${escapeHtml(c.judul || 'Percakapan Baru')}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(c.nama_user)}</td>
                <td class="px-4 py-3 text-right">${fmtNum(c.request_count)}</td>
                <td class="px-4 py-3 text-right font-medium">${fmtNum(c.total_tokens)}</td>
                <td class="px-4 py-3 text-right text-gray-500">${fmtNum(c.avg_latency)} ms</td>
                <td class="px-4 py-3 text-gray-500 text-xs">${fmtDate(c.last_activity)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="openConversation(${c.id_conversation})"
                        class="px-3 py-1 bg-violet-100 text-violet-700 rounded-lg text-xs hover:bg-violet-200">
                        Lihat
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderErrors(errors) {
        const tbody = document.getElementById('errors-table');
        if (!errors.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Tidak ada error 🎉</td></tr>';
            return;
        }
        tbody.innerHTML = errors.map(e => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">${fmtDate(e.created_at)}</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(e.nama_user)}</td>
                <td class="px-4 py-3 text-red-600 text-xs">${escapeHtml(e.error_message || '-')}</td>
            </tr>
        `).join('');
    }

    async function openConversation(id) {
        const modal = document.getElementById('modal-conversation');
        const body = document.getElementById('modal-body');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        body.innerHTML = '<div class="flex justify-center py-10"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-violet-600"></div></div>';

        try {
            const res = await fetch(`<?= BASEURL; ?>/admin/aiMonitoringConversation/${id}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            const conv = data.data.conversation;
            document.getElementById('modal-title').textContent = conv.judul || 'Percakapan Baru';

            let html = `<div class="text-sm text-gray-500 mb-3 pb-3 border-b">
                <span class="font-medium text-gray-700">${escapeHtml(conv.nama_user)}</span> · ${escapeHtml(conv.role)} · ${fmtDate(conv.created_at)}
            </div>`;

            data.data.messages.forEach(m => {
                const isUser = m.role === 'user';
                html += `<div class="flex ${isUser ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[80%] rounded-xl px-4 py-2.5 text-sm ${isUser ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-800'}">
                        <div class="text-xs opacity-70 mb-1">${isUser ? 'User' : 'AI'} · ${fmtDate(m.created_at)}</div>
                        <div class="whitespace-pre-wrap break-words">${escapeHtml(m.content)}</div>
                    </div>
                </div>`;
            });

            body.innerHTML = html;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            body.innerHTML = '<p class="text-red-600 text-center py-10">Error: ' + e.message + '</p>';
        }
    }

    function closeModal() {
        const modal = document.getElementById('modal-conversation');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Tutup modal dengan ESC
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
