<?php
/**
 * Enhanced Dashboard View
 * Beautiful, interactive dashboard with statistics and visualizations
 * Uses Chart.js for dynamic charts and Bootstrap 5 for responsive layout
 */
?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --primary-cyan: #00D9FF;
        --primary-blue: #0099FF;
        --success-green: #00D964;
        --danger-red: #FF6B6B;
        --warning-yellow: #FFD93D;
        --dark-bg: #1a1a2e;
        --card-bg: #16213e;
        --border-color: #0f3460;
        --text-primary: #eaeaea;
        --text-secondary: #b0b0b0;
    }

    body.light-mode {
        --dark-bg: #f8f9fa;
        --card-bg: #ffffff;
        --border-color: #e9ecef;
        --text-primary: #212529;
        --text-secondary: #6c757d;
    }

    .dashboard-container {
        background: var(--dark-bg);
        color: var(--text-primary);
        min-height: 100vh;
        padding: 2rem 1rem;
        transition: all 0.3s ease;
    }

    .dashboard-header {
        margin-bottom: 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .dashboard-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--primary-blue) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }

    .header-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
    }

    .header-stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(0, 217, 255, 0.1);
        border-radius: 8px;
        border-left: 3px solid var(--primary-cyan);
    }

    /* Stat Cards */
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 217, 255, 0.15);
        border-color: var(--primary-cyan);
    }

    .stat-card.cyan {
        border-left: 4px solid var(--primary-cyan);
    }

    .stat-card.blue {
        border-left: 4px solid var(--primary-blue);
    }

    .stat-card.green {
        border-left: 4px solid var(--success-green);
    }

    .stat-card.yellow {
        border-left: 4px solid var(--warning-yellow);
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-card-title {
        font-size: 0.9rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .stat-card-icon {
        font-size: 2rem;
    }

    .stat-card-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 1rem 0;
        color: var(--text-primary);
    }

    .stat-card-footer {
        font-size: 0.85rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-card-footer .badge {
        background: rgba(0, 217, 255, 0.2);
        color: var(--primary-cyan);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Charts */
    .chart-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
    }

    .chart-card h3 {
        margin: 0 0 1.5rem 0;
        font-size: 1.2rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-card-icon {
        font-size: 1.5rem;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 1rem;
    }

    /* Progress Bars */
    .progress-item {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .progress-label {
        min-width: 120px;
        font-weight: 500;
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    .progress {
        flex: 1;
        height: 10px;
        background: var(--border-color);
        border-radius: 10px;
        margin: 0 1rem;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-cyan), var(--primary-blue));
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .progress-percentage {
        min-width: 50px;
        text-align: right;
        font-weight: 600;
        color: var(--primary-cyan);
        font-size: 0.9rem;
    }

    /* Lists */
    .donor-list, .city-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s ease;
    }

    .list-item:hover {
        background: rgba(0, 217, 255, 0.05);
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .list-item-name {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        color: var(--text-primary);
        flex: 1;
    }

    .list-item-badge {
        font-size: 1.5rem;
    }

    .list-item-value {
        font-weight: 700;
        color: var(--primary-cyan);
        min-width: 100px;
        text-align: right;
    }

    .donor-rank {
        display: inline-block;
        width: 30px;
        height: 30px;
        background: rgba(0, 217, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--primary-cyan);
    }

    /* Summary Cards */
    .summary-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary-cyan);
    }

    .summary-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .summary-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stat-card-value {
            font-size: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .stat-card {
            padding: 1.5rem;
        }

        .chart-card {
            padding: 1.5rem;
        }

        .stat-card-value {
            font-size: 1.75rem;
        }

        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-stats {
            flex-wrap: wrap;
        }

        .progress-label {
            min-width: 100px;
            font-size: 0.8rem;
        }

        .list-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .list-item-value {
            align-self: flex-start;
            margin-top: 0.5rem;
        }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--border-color);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-cyan);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-blue);
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <h1>📊 Dashboard</h1>
        <div class="header-stats">
            <div class="header-stat">
                <span>🕐 Last Updated: <strong><?= date('M d, Y H:i') ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Row 1: Key Metrics -->
    <div class="row">
        <!-- Total Beneficiaries -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card cyan">
                <div class="stat-card-header">
                    <div class="stat-card-title">👥 Total Beneficiaries</div>
                    <div class="stat-card-icon">📋</div>
                </div>
                <div class="stat-card-value"><?= number_format($data['total_beneficiaries']) ?></div>
                <div class="stat-card-footer">
                    <span class="badge">Active: <?= $data['active_beneficiaries'] ?></span>
                </div>
            </div>
        </div>

        <!-- Active Rate -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card blue">
                <div class="stat-card-header">
                    <div class="stat-card-title">📈 Active Rate</div>
                    <div class="stat-card-icon">⚡</div>
                </div>
                <div class="stat-card-value">
                    <?php
                    $activeRate = $data['total_beneficiaries'] > 0 ?
                        round(($data['active_beneficiaries'] / $data['total_beneficiaries']) * 100) : 0;
                    echo $activeRate . '%';
                    ?>
                </div>
                <div class="stat-card-footer">
                    <span><?= $data['active_beneficiaries'] ?> of <?= $data['total_beneficiaries'] ?></span>
                </div>
            </div>
        </div>

        <!-- Total Contributions -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card green">
                <div class="stat-card-header">
                    <div class="stat-card-title">💰 Total Contributions</div>
                    <div class="stat-card-icon">💵</div>
                </div>
                <div class="stat-card-value">₹<?= number_format($data['total_contributions'] / 100000, 1) ?>L</div>
                <div class="stat-card-footer">
                    <span class="badge">From <?= $data['total_donors'] ?> donors</span>
                </div>
            </div>
        </div>

        <!-- Average Donation -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card yellow">
                <div class="stat-card-header">
                    <div class="stat-card-title">🎁 Avg. Donation</div>
                    <div class="stat-card-icon">🏆</div>
                </div>
                <div class="stat-card-value">₹<?= number_format($data['average_donation'], 0) ?></div>
                <div class="stat-card-footer">
                    <span>Per donor</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Charts 1 & 2 -->
    <div class="row">
        <!-- Beneficiary Status Distribution -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">📊</span> Beneficiary Status Distribution</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gender Distribution -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">👫</span> Gender Distribution</h3>
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Charts 3 & 4 -->
    <div class="row">
        <!-- Registration Trend -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">📈</span> Registration Trend (Last 6 Months)</h3>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Donor Type Breakdown -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">💼</span> Donor Type Breakdown</h3>
                <div class="chart-container">
                    <canvas id="donorTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Distribution Analysis -->
    <div class="row">
        <!-- Age Group Distribution -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">🎂</span> Age Group Distribution</h3>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ID Type Distribution -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">🆔</span> ID Type Distribution</h3>
                <div style="padding: 1rem 0;">
                    <div class="progress-item">
                        <span class="progress-label">Aadhaar</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $data['id_aadhaar_percent'] ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= round($data['id_aadhaar_percent']) ?>%</span>
                    </div>
                    <div class="progress-item">
                        <span class="progress-label">PAN</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $data['id_pan_percent'] ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= round($data['id_pan_percent']) ?>%</span>
                    </div>
                    <div class="progress-item">
                        <span class="progress-label">Voter ID</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $data['id_voter_percent'] ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= round($data['id_voter_percent']) ?>%</span>
                    </div>
                    <div class="progress-item">
                        <span class="progress-label">Other</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $data['id_other_percent'] ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= round($data['id_other_percent']) ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 5: Detailed Insights -->
    <div class="row">
        <!-- Top 5 Donors -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">🥇</span> Top 5 Donors by Contribution</h3>
                <div class="donor-list">
                    <?php
                    $badges = ['🥇', '🥈', '🥉', '⭐', '✨'];
                    foreach ($data['top_donors'] as $index => $donor):
                    ?>
                    <div class="list-item">
                        <div class="list-item-name">
                            <span class="list-item-badge"><?= $badges[$index] ?? '💝' ?></span>
                            <div>
                                <div><?= htmlspecialchars($donor['donor_name']) ?></div>
                                <small style="color: var(--text-secondary);"><?= ucfirst($donor['donor_type']) ?></small>
                            </div>
                        </div>
                        <div class="list-item-value">₹<?= number_format($donor['total_contribution']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Beneficiaries by City -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><span class="chart-card-icon">🏙️</span> Beneficiaries by City</h3>
                <div class="city-list">
                    <?php
                    $totalBeneficiaries = $data['total_beneficiaries'];
                    foreach ($data['beneficiaries_by_city'] as $city => $count):
                        $percentage = ($count / $totalBeneficiaries) * 100;
                    ?>
                    <div class="progress-item">
                        <span class="progress-label"><?= htmlspecialchars($city) ?></span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= $count ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 6: Summary Cards -->
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="summary-card">
                <div class="summary-icon">👥</div>
                <div class="summary-label">Total Users in System</div>
                <div class="summary-value"><?= $data['total_users'] ?></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="summary-card">
                <div class="summary-icon">🆕</div>
                <div class="summary-label">Last Registration</div>
                <div class="summary-value" style="font-size: 1.2rem;"><?= date('M d, Y', strtotime($data['last_beneficiary_registered'])) ?></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="summary-card">
                <div class="summary-icon">✅</div>
                <div class="summary-label">System Status</div>
                <div class="summary-value" style="color: var(--success-green);"><?= $data['system_health_status'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // Chart Colors
    const colors = {
        cyan: 'rgba(0, 217, 255, 1)',
        blue: 'rgba(0, 153, 255, 1)',
        green: 'rgba(0, 217, 100, 1)',
        yellow: 'rgba(255, 217, 61, 1)',
        red: 'rgba(255, 107, 107, 1)',
        pink: 'rgba(255, 182, 193, 1)',
    };

    const bgColors = {
        cyan: 'rgba(0, 217, 255, 0.1)',
        blue: 'rgba(0, 153, 255, 0.1)',
        green: 'rgba(0, 217, 100, 0.1)',
        yellow: 'rgba(255, 217, 61, 0.1)',
        red: 'rgba(255, 107, 107, 0.1)',
        pink: 'rgba(255, 182, 193, 0.1)',
    };

    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: 'var(--text-primary)',
                    font: { size: 12, weight: '500' },
                    padding: 15,
                },
                position: 'bottom'
            },
        },
        scales: {
            y: {
                ticks: { color: 'var(--text-secondary)' },
                grid: { color: 'var(--border-color)' },
                border: { color: 'var(--border-color)' },
            },
            x: {
                ticks: { color: 'var(--text-secondary)' },
                grid: { color: 'var(--border-color)' },
                border: { color: 'var(--border-color)' },
            }
        }
    };

    // 1. Beneficiary Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Registered', 'Inactive'],
            datasets: [{
                data: [
                    <?= $data['active_beneficiaries'] ?>,
                    <?= $data['registered_beneficiaries'] ?>,
                    <?= $data['inactive_beneficiaries'] ?>
                ],
                backgroundColor: [colors.green, colors.blue, colors.red],
                borderColor: ['var(--card-bg)', 'var(--card-bg)', 'var(--card-bg)'],
                borderWidth: 3,
            }]
        },
        options: {
            ...chartConfig,
            plugins: {
                ...chartConfig.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });

    // 2. Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: ['Female', 'Male', 'Other'],
            datasets: [{
                data: [
                    <?= $data['gender_female'] ?>,
                    <?= $data['gender_male'] ?>,
                    <?= $data['gender_other'] ?>
                ],
                backgroundColor: [colors.pink, colors.blue, colors.yellow],
                borderColor: ['var(--card-bg)', 'var(--card-bg)', 'var(--card-bg)'],
                borderWidth: 3,
            }]
        },
        options: {
            ...chartConfig,
            plugins: {
                ...chartConfig.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });

    // 3. Registration Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($data['registration_trend']['months']) ?>,
            datasets: [{
                label: 'New Registrations',
                data: <?= json_encode($data['registration_trend']['counts']) ?>,
                borderColor: colors.cyan,
                backgroundColor: bgColors.cyan,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: colors.cyan,
                pointBorderColor: 'var(--card-bg)',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
            }]
        },
        options: {
            ...chartConfig,
            scales: {
                ...chartConfig.scales,
                y: {
                    ...chartConfig.scales.y,
                    beginAtZero: true,
                }
            }
        }
    });

    // 4. Donor Type Chart
    const donorTypeCtx = document.getElementById('donorTypeChart').getContext('2d');
    new Chart(donorTypeCtx, {
        type: 'bar',
        data: {
            labels: ['Individual', 'Organization', 'Corporate'],
            datasets: [{
                label: 'Number of Donors',
                data: [
                    <?= $data['donors_individual'] ?>,
                    <?= $data['donors_organization'] ?>,
                    <?= $data['donors_corporate'] ?>
                ],
                backgroundColor: [colors.blue, colors.cyan, colors.green],
                borderColor: [colors.blue, colors.cyan, colors.green],
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: {
            ...chartConfig,
            indexAxis: 'y',
            scales: {
                x: {
                    ...chartConfig.scales.x,
                    beginAtZero: true,
                },
            }
        }
    });

    // 5. Age Group Distribution Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: ['Under 18', '18-25', '26-35', '36-45', '46-55', '56+'],
            datasets: [{
                label: 'Count',
                data: [
                    <?= $data['age_under_18'] ?? 0 ?>,
                    <?= $data['age_18_25'] ?? 0 ?>,
                    <?= $data['age_26_35'] ?? 0 ?>,
                    <?= $data['age_36_45'] ?? 0 ?>,
                    <?= $data['age_46_55'] ?? 0 ?>,
                    <?= $data['age_56_plus'] ?? 0 ?>
                ],
                backgroundColor: [
                    colors.red,
                    colors.yellow,
                    colors.green,
                    colors.cyan,
                    colors.blue,
                    colors.pink
                ],
                borderColor: [
                    colors.red,
                    colors.yellow,
                    colors.green,
                    colors.cyan,
                    colors.blue,
                    colors.pink
                ],
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: {
            ...chartConfig,
            scales: {
                y: {
                    ...chartConfig.scales.y,
                    beginAtZero: true,
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>
