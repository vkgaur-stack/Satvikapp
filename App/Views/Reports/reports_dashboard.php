<?php
/**
 * Reports Dashboard View
 * Advanced analytics and reporting interface
 * Provides comprehensive insights into beneficiary and donor data
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

    .reports-container {
        background: var(--dark-bg);
        color: var(--text-primary);
        min-height: 100vh;
        padding: 2rem 1rem;
        transition: all 0.3s ease;
    }

    .reports-header {
        margin-bottom: 3rem;
    }

    .reports-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--success-green) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0 0 1rem 0;
    }

    .reports-header-desc {
        font-size: 1rem;
        color: var(--text-secondary);
    }

    /* Navigation Tabs */
    .reports-nav {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 1rem;
    }

    .report-tab-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .report-tab-btn:hover {
        color: var(--primary-cyan);
    }

    .report-tab-btn.active {
        color: var(--primary-cyan);
    }

    .report-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary-cyan);
    }

    /* KPI Cards */
    .kpi-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 217, 255, 0.1);
        border-color: var(--primary-cyan);
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .kpi-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .kpi-icon {
        font-size: 1.5rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .kpi-change {
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .kpi-change.positive {
        color: var(--success-green);
    }

    .kpi-change.negative {
        color: var(--danger-red);
    }

    /* Report Card */
    .report-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .report-card-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .report-card-icon {
        font-size: 1.5rem;
    }

    /* Table Styles */
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .report-table thead {
        background: rgba(0, 217, 255, 0.05);
        border-bottom: 2px solid var(--border-color);
    }

    .report-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .report-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .report-table tbody tr:hover {
        background: rgba(0, 217, 255, 0.05);
    }

    .report-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background: rgba(0, 217, 100, 0.2);
        color: var(--success-green);
    }

    .badge-danger {
        background: rgba(255, 107, 107, 0.2);
        color: var(--danger-red);
    }

    .badge-warning {
        background: rgba(255, 217, 61, 0.2);
        color: var(--warning-yellow);
    }

    .badge-info {
        background: rgba(0, 153, 255, 0.2);
        color: var(--primary-blue);
    }

    /* Export Button */
    .export-btn {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #000;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 217, 255, 0.3);
    }

    /* Section Container */
    .report-section {
        display: none;
    }

    .report-section.active {
        display: block;
    }

    /* Metric Grid */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .reports-header h1 {
            font-size: 1.8rem;
        }

        .reports-nav {
            flex-direction: column;
        }

        .report-tab-btn {
            width: 100%;
            text-align: left;
        }

        .report-table {
            font-size: 0.85rem;
        }

        .report-table th, .report-table td {
            padding: 0.75rem;
        }

        .metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <h1>📊 Analytics & Reports</h1>
        <p class="reports-header-desc">
            Comprehensive insights into beneficiary and donor data with detailed analytics
        </p>
    </div>

    <!-- Navigation Tabs -->
    <div class="reports-nav">
        <button class="report-tab-btn active" onclick="switchTab('overview')">📈 Overview</button>
        <button class="report-tab-btn" onclick="switchTab('beneficiaries')">👥 Beneficiaries</button>
        <button class="report-tab-btn" onclick="switchTab('donors')">💼 Donors</button>
        <button class="report-tab-btn" onclick="switchTab('financial')">💰 Financial</button>
        <button class="report-tab-btn" onclick="switchTab('impact')">🎯 Impact</button>
    </div>

    <!-- ===== OVERVIEW TAB ===== -->
    <div id="overview" class="report-section active">
        <!-- Key KPIs -->
        <div class="metric-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Beneficiaries</div>
                    <div class="kpi-icon">👥</div>
                </div>
                <div class="kpi-value"><?= number_format($beneficiary_stats['total']) ?></div>
                <div class="kpi-change positive">
                    ⬆️ <?= $key_kpis['beneficiary_growth_rate'] ?> growth this month
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Donors</div>
                    <div class="kpi-icon">💼</div>
                </div>
                <div class="kpi-value"><?= number_format($donor_stats['total']) ?></div>
                <div class="kpi-change positive">
                    ⬆️ <?= $key_kpis['donor_retention_rate'] ?>% retention
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Contributions</div>
                    <div class="kpi-icon">💰</div>
                </div>
                <div class="kpi-value">₹<?= number_format($financial_summary['total_revenue'] / 100000, 1) ?>L</div>
                <div class="kpi-change positive">
                    ⬆️ Avg: ₹<?= number_format($financial_summary['average_donation']) ?>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Active Programs</div>
                    <div class="kpi-icon">📋</div>
                </div>
                <div class="kpi-value"><?= $program_metrics['active_programs'] ?></div>
                <div class="kpi-change positive">
                    ⬆️ <?= $key_kpis['program_success_rate'] ?>% success rate
                </div>
            </div>
        </div>

        <!-- Monthly Summary -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">📅</span>
                Current Month Summary
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>New Beneficiaries Added</td>
                        <td><strong><?= $monthly_summary['new_beneficiaries'] ?></strong></td>
                        <td><span class="badge badge-success">On Track</span></td>
                    </tr>
                    <tr>
                        <td>New Donors</td>
                        <td><strong><?= $monthly_summary['new_donors'] ?></strong></td>
                        <td><span class="badge badge-info">Active</span></td>
                    </tr>
                    <tr>
                        <td>Funds Received</td>
                        <td><strong>₹<?= number_format($monthly_summary['funds_received']) ?></strong></td>
                        <td><span class="badge badge-success">Positive</span></td>
                    </tr>
                    <tr>
                        <td>Services Delivered</td>
                        <td><strong><?= $monthly_summary['services_delivered'] ?></strong></td>
                        <td><span class="badge badge-success">Excellent</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- KPI Metrics -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">📊</span>
                Key Performance Indicators
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>KPI</th>
                        <th>Target</th>
                        <th>Current</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Beneficiary Growth Rate</td>
                        <td>15%</td>
                        <td><?= $key_kpis['beneficiary_growth_rate'] ?></td>
                        <td><span class="badge badge-success">On Track</span></td>
                    </tr>
                    <tr>
                        <td>Donor Retention Rate</td>
                        <td>80%</td>
                        <td><?= $key_kpis['donor_retention_rate'] ?>%</td>
                        <td><span class="badge badge-success">Exceeding</span></td>
                    </tr>
                    <tr>
                        <td>Fund Utilization Rate</td>
                        <td>85%</td>
                        <td><?= $key_kpis['fund_utilization_rate'] ?></td>
                        <td><span class="badge badge-success">Healthy</span></td>
                    </tr>
                    <tr>
                        <td>Program Success Rate</td>
                        <td>80%</td>
                        <td><?= $key_kpis['program_success_rate'] ?>%</td>
                        <td><span class="badge badge-success">Excellent</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== BENEFICIARIES TAB ===== -->
    <div id="beneficiaries" class="report-section">
        <div class="metric-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Beneficiaries</div>
                    <div class="kpi-icon">👥</div>
                </div>
                <div class="kpi-value"><?= number_format($beneficiary_stats['total']) ?></div>
                <div class="kpi-change positive">
                    ⬆️ Active: <?= $beneficiary_stats['active'] ?>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Active Rate</div>
                    <div class="kpi-icon">⚡</div>
                </div>
                <div class="kpi-value">
                    <?php
                    $activeRate = $beneficiary_stats['total'] > 0 ?
                        round(($beneficiary_stats['active'] / $beneficiary_stats['total']) * 100) : 0;
                    echo $activeRate . '%';
                    ?>
                </div>
                <div class="kpi-change positive">
                    📈 Healthy retention
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Inactive</div>
                    <div class="kpi-icon">❌</div>
                </div>
                <div class="kpi-value"><?= $beneficiary_stats['inactive'] ?></div>
                <div class="kpi-change negative">
                    Need follow-up
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Registered (Pending)</div>
                    <div class="kpi-icon">🔔</div>
                </div>
                <div class="kpi-value"><?= $beneficiary_stats['total'] - $beneficiary_stats['active'] - $beneficiary_stats['inactive'] ?></div>
                <div class="kpi-change positive">
                    📥 Awaiting activation
                </div>
            </div>
        </div>

        <!-- Beneficiary Status Breakdown -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">📊</span>
                Status Breakdown
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Active</strong></td>
                        <td><?= $beneficiary_stats['active'] ?></td>
                        <td>
                            <?php
                            $percent = $beneficiary_stats['total'] > 0 ?
                                round(($beneficiary_stats['active'] / $beneficiary_stats['total']) * 100) : 0;
                            echo $percent . '%';
                            ?>
                        </td>
                        <td><span class="badge badge-success">Monitor</span></td>
                    </tr>
                    <tr>
                        <td><strong>Registered</strong></td>
                        <td><?= $beneficiary_stats['total'] - $beneficiary_stats['active'] - $beneficiary_stats['inactive'] ?></td>
                        <td>
                            <?php
                            $reg = $beneficiary_stats['total'] - $beneficiary_stats['active'] - $beneficiary_stats['inactive'];
                            $percent = $beneficiary_stats['total'] > 0 ?
                                round(($reg / $beneficiary_stats['total']) * 100) : 0;
                            echo $percent . '%';
                            ?>
                        </td>
                        <td><span class="badge badge-info">Engage</span></td>
                    </tr>
                    <tr>
                        <td><strong>Inactive</strong></td>
                        <td><?= $beneficiary_stats['inactive'] ?></td>
                        <td>
                            <?php
                            $percent = $beneficiary_stats['total'] > 0 ?
                                round(($beneficiary_stats['inactive'] / $beneficiary_stats['total']) * 100) : 0;
                            echo $percent . '%';
                            ?>
                        </td>
                        <td><span class="badge badge-warning">Follow Up</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Demographic Info -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">👤</span>
                Demographic Analysis
            </div>
            <table class="report-table">
                <tbody>
                    <tr>
                        <td><strong>Average Household Size</strong></td>
                        <td><?= $beneficiary_stats['total'] > 0 ? '4.2 members' : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Rural Beneficiaries</strong></td>
                        <td><?= round(65) ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Urban Beneficiaries</strong></td>
                        <td><?= round(35) ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Average Annual Income</strong></td>
                        <td>₹<?= number_format(125000) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== DONORS TAB ===== -->
    <div id="donors" class="report-section">
        <div class="metric-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Donors</div>
                    <div class="kpi-icon">💼</div>
                </div>
                <div class="kpi-value"><?= number_format($donor_stats['total']) ?></div>
                <div class="kpi-change positive">
                    ⬆️ Active: <?= $donor_stats['active'] ?>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Active Donors</div>
                    <div class="kpi-icon">⭐</div>
                </div>
                <div class="kpi-value">
                    <?php
                    $activePercent = $donor_stats['total'] > 0 ?
                        round(($donor_stats['active'] / $donor_stats['total']) * 100) : 0;
                    echo $activePercent . '%';
                    ?>
                </div>
                <div class="kpi-change positive">
                    ✅ Engagement healthy
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Donations</div>
                    <div class="kpi-icon">💝</div>
                </div>
                <div class="kpi-value">₹<?= number_format($donor_stats['total_donations'] / 100000, 1) ?>L</div>
                <div class="kpi-change positive">
                    📈 Growing base
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Avg Donation</div>
                    <div class="kpi-icon">🎁</div>
                </div>
                <div class="kpi-value">₹<?= number_format($financial_summary['average_donation']) ?></div>
                <div class="kpi-change positive">
                    💪 Strong support
                </div>
            </div>
        </div>

        <!-- Donor Type Breakdown -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">📊</span>
                Donor Type Distribution
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Donor Type</th>
                        <th>Count</th>
                        <th>Percentage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Individual Donors</strong></td>
                        <td>4</td>
                        <td>40%</td>
                        <td><span class="badge badge-success">Growing</span></td>
                    </tr>
                    <tr>
                        <td><strong>Organizations</strong></td>
                        <td>3</td>
                        <td>30%</td>
                        <td><span class="badge badge-info">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>Corporate</strong></td>
                        <td>3</td>
                        <td>30%</td>
                        <td><span class="badge badge-success">Major Donors</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Top Donors -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">🏆</span>
                Top 5 Donors
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Donor Name</th>
                        <th>Type</th>
                        <th>Total Contribution</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>🥇</strong></td>
                        <td>TechCorp Industries</td>
                        <td><span class="badge badge-info">Corporate</span></td>
                        <td><strong>₹10,000,000</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>🥈</strong></td>
                        <td>Global Foundation</td>
                        <td><span class="badge badge-info">Organization</span></td>
                        <td><strong>₹1,000,000</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>🥉</strong></td>
                        <td>Healthcare Plus</td>
                        <td><span class="badge badge-info">Organization</span></td>
                        <td><strong>₹500,000</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>4️⃣</strong></td>
                        <td>Rajesh Kumar</td>
                        <td><span class="badge badge-info">Individual</span></td>
                        <td><strong>₹25,000</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>5️⃣</strong></td>
                        <td>Development NGO</td>
                        <td><span class="badge badge-info">Organization</span></td>
                        <td><strong>₹100,000</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== FINANCIAL TAB ===== -->
    <div id="financial" class="report-section">
        <div class="metric-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-icon">💰</div>
                </div>
                <div class="kpi-value">₹<?= number_format($financial_summary['total_revenue'] / 100000, 1) ?>L</div>
                <div class="kpi-change positive">
                    ⬆️ From <?= $donor_stats['total'] ?> donors
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Average Donation</div>
                    <div class="kpi-icon">🎁</div>
                </div>
                <div class="kpi-value">₹<?= number_format($financial_summary['average_donation']) ?></div>
                <div class="kpi-change positive">
                    📊 Per donation
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Fund Utilization</div>
                    <div class="kpi-icon">📈</div>
                </div>
                <div class="kpi-value">85%</div>
                <div class="kpi-change positive">
                    ✅ Healthy allocation
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Cost Per Beneficiary</div>
                    <div class="kpi-icon">💵</div>
                </div>
                <div class="kpi-value">
                    ₹<?php
                    echo number_format($financial_summary['total_revenue'] / ($beneficiary_stats['total'] > 0 ? $beneficiary_stats['total'] : 1));
                    ?>
                </div>
                <div class="kpi-change positive">
                    Efficient spend
                </div>
            </div>
        </div>

        <!-- Financial Summary Table -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">📊</span>
                Financial Summary
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Amount</th>
                        <th>Percentage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Revenue Received</strong></td>
                        <td>₹<?= number_format($financial_summary['total_revenue']) ?></td>
                        <td>100%</td>
                        <td><span class="badge badge-success">Complete</span></td>
                    </tr>
                    <tr>
                        <td><strong>Funds Allocated</strong></td>
                        <td>₹<?= number_format($financial_summary['total_revenue'] * 0.85) ?></td>
                        <td>85%</td>
                        <td><span class="badge badge-success">On Track</span></td>
                    </tr>
                    <tr>
                        <td><strong>Administrative Cost</strong></td>
                        <td>₹<?= number_format($financial_summary['total_revenue'] * 0.10) ?></td>
                        <td>10%</td>
                        <td><span class="badge badge-success">Optimal</span></td>
                    </tr>
                    <tr>
                        <td><strong>Reserve Fund</strong></td>
                        <td>₹<?= number_format($financial_summary['total_revenue'] * 0.05) ?></td>
                        <td>5%</td>
                        <td><span class="badge badge-info">Safety Net</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== IMPACT TAB ===== -->
    <div id="impact" class="report-section">
        <div class="metric-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Families Impacted</div>
                    <div class="kpi-icon">👨‍👩‍👧‍👦</div>
                </div>
                <div class="kpi-value"><?= number_format($beneficiary_stats['total']) ?></div>
                <div class="kpi-change positive">
                    📈 Lives changed
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Active Programs</div>
                    <div class="kpi-icon">📋</div>
                </div>
                <div class="kpi-value"><?= $program_metrics['active_programs'] ?></div>
                <div class="kpi-change positive">
                    ⭐ <?= $key_kpis['program_success_rate'] ?>% success
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Services Delivered</div>
                    <div class="kpi-icon">🎯</div>
                </div>
                <div class="kpi-value">250+</div>
                <div class="kpi-change positive">
                    ✅ Direct assistance
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-label">Impact Rating</div>
                    <div class="kpi-icon">⭐</div>
                </div>
                <div class="kpi-value">4.5/5</div>
                <div class="kpi-change positive">
                    🏆 Excellent impact
                </div>
            </div>
        </div>

        <!-- Impact Outcomes -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">🎯</span>
                Outcome Achievements
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Outcome Area</th>
                        <th>Achievement Rate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Education Goals</strong></td>
                        <td>78%</td>
                        <td><span class="badge badge-success">Strong Progress</span></td>
                    </tr>
                    <tr>
                        <td><strong>Health Improvement</strong></td>
                        <td>82%</td>
                        <td><span class="badge badge-success">Excellent</span></td>
                    </tr>
                    <tr>
                        <td><strong>Income Increase</strong></td>
                        <td>65%</td>
                        <td><span class="badge badge-warning">Good</span></td>
                    </tr>
                    <tr>
                        <td><strong>Skill Development</strong></td>
                        <td>75%</td>
                        <td><span class="badge badge-success">On Track</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Environmental Impact -->
        <div class="report-card">
            <div class="report-card-title">
                <span class="report-card-icon">🌱</span>
                Environmental Impact
            </div>
            <table class="report-table">
                <tbody>
                    <tr>
                        <td><strong>Trees Planted</strong></td>
                        <td><strong>1,250+</strong> 🌳</td>
                    </tr>
                    <tr>
                        <td><strong>Waste Recycled</strong></td>
                        <td><strong>3,500 kg</strong> ♻️</td>
                    </tr>
                    <tr>
                        <td><strong>Water Saved</strong></td>
                        <td><strong>25,000 liters</strong> 💧</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Section -->
    <div style="margin-top: 3rem; text-align: center;">
        <button class="export-btn" onclick="exportReport()">📥 Export Full Report (PDF)</button>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all sections
        const sections = document.querySelectorAll('.report-section');
        sections.forEach(section => section.classList.remove('active'));

        // Remove active class from all buttons
        const buttons = document.querySelectorAll('.report-tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Show selected section
        const selectedSection = document.getElementById(tabName);
        if (selectedSection) {
            selectedSection.classList.add('active');
        }

        // Add active class to clicked button
        event.target.classList.add('active');
    }

    function exportReport() {
        alert('📥 Export to PDF feature coming soon!\n\nFull report will include:\n✓ All analytics\n✓ Detailed tables\n✓ Charts & graphs\n✓ Summary metrics');
        // Implementation: window.open('/reports/export-pdf');
    }
</script>

<?= $this->endSection() ?>
