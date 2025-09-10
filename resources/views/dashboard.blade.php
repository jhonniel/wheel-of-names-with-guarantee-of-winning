<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wheel of Names</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .header-actions a {
            color: #667eea;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 2px solid #667eea;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .header-actions a:hover {
            background: #667eea;
            color: white;
        }
        .logout-form {
            display: inline;
        }
        .logout-button {
            background: #dc3545;
            color: white;
            border: 2px solid #dc3545;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .logout-button:hover {
            background: #c82333;
            border-color: #c82333;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .card h3 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1.25rem;
        }
        .action-button {
            display: block;
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .action-button.blue {
            background: #007bff;
            color: white;
        }
        .action-button.blue:hover {
            background: #0056b3;
        }
        .action-button.green {
            background: #28a745;
            color: white;
        }
        .action-button.green:hover {
            background: #1e7e34;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-label {
            color: #666;
        }
        .stat-value {
            font-weight: 600;
            color: #333;
        }
        .recent-spin {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .recent-spin:last-child {
            border-bottom: none;
        }
        .spin-name {
            font-weight: 500;
            color: #333;
        }
        .spin-time {
            color: #666;
            font-size: 0.875rem;
        }
        .welcome-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .welcome-card h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        .welcome-card p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 Wheel of Names - Admin Dashboard</h1>
        <div class="header-actions">
            <a href="/">View Wheel</a>
            <a href="/participants">Manage Participants</a>
            <a href="{{ route('register') }}">Create Admin</a>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="grid">
            <!-- Quick Actions -->
            <div class="card">
                <h3>Quick Actions</h3>
                <a href="/" class="action-button blue">🎯 View Wheel</a>
                <a href="/participants" class="action-button green">⚙️ Manage Participants</a>
                <a href="{{ route('register') }}" class="action-button" style="background: #6f42c1; color: white; margin-bottom: 0.5rem;">👤 Create New Admin</a>
            </div>

            <!-- Statistics -->
            <div class="card">
                <h3>Statistics</h3>
                <div class="stat-item">
                    <span class="stat-label">Total Participants:</span>
                    <span class="stat-value">{{ \App\Models\Participant::count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Active Participants:</span>
                    <span class="stat-value">{{ \App\Models\Participant::where('active', true)->count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Spins:</span>
                    <span class="stat-value">{{ \App\Models\Spin::count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Spin Duration:</span>
                    <span class="stat-value">{{ \App\Models\WheelSetting::getSpinDuration() }}s</span>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <h3>Recent Spins</h3>
                @forelse(\App\Models\Spin::with('participant')->latest()->take(5)->get() as $spin)
                    <div class="recent-spin">
                        <div class="spin-name">{{ $spin->participant->name }}</div>
                        <div class="spin-time">{{ $spin->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <p style="color: #666; margin: 0;">No spins yet</p>
                @endforelse
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="welcome-card">
            <h3>Welcome, {{ Auth::user()->name }}!</h3>
            <p>
                You're now logged in as an administrator. You can manage participants, adjust settings, and monitor the wheel activity from this dashboard.
            </p>
        </div>
    </div>
</body>
</html>
