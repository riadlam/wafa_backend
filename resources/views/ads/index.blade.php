<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advertisements</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/build/assets/app.css">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji"; background:#f8fafc; }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card { background:#fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 16px; }
        table { width:100%; border-collapse: collapse; }
        th, td { text-align:left; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; }
        th { font-size:12px; text-transform: uppercase; color:#6b7280; letter-spacing: 0.06em; }
        .title { font-weight:600; color:#111827; }
        .muted { color:#6b7280; font-size:12px; }
        .badge { display:inline-block; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .badge-pending { background:#fff7ed; color:#c2410c; }
        .badge-approved { background:#ecfeff; color:#155e75; }
        .badge-rejected { background:#fef2f2; color:#991b1b; }
        .badge-sent { background:#f0fdf4; color:#166534; }
        .badge-failed { background:#fff1f2; color:#9f1239; }
        .toolbar { display:flex; gap:8px; margin-bottom:12px; align-items:center; }
        .toolbar a { text-decoration:none; font-size:12px; padding:6px 10px; border-radius:8px; background:#e5e7eb; color:#111827; }
        .toolbar a.active { background:#111827; color:#fff; }
    </style>
    <script defer src="/build/assets/app.js"></script>
  </head>
  <body>
    <div class="container">
      <!-- Navigation Header -->
      <div style="background:#fff; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.06); padding:16px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h1 style="margin:0; font-size:20px; font-weight:700; color:#111827;">Wafa Loyalty Dashboard</h1>
          <div style="display:flex; gap:8px;">
            <a href="/users" style="text-decoration:none; padding:8px 16px; border-radius:8px; background:#3b82f6; color:#fff; font-size:14px; font-weight:500;">👥 Users</a>
            <a href="/shop-owners" style="text-decoration:none; padding:8px 16px; border-radius:8px; background:#10b981; color:#fff; font-size:14px; font-weight:500;">🏪 Shop Owners</a>
          </div>
        </div>
      </div>

      <!-- Overall Statistics Cards -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="card" style="text-align:center;">
          <div style="font-size:24px; font-weight:700; color:#3b82f6; margin-bottom:4px;">
            {{ ($stats['today']['users'] ?? 0) + ($stats['last_15_days']['users'] ?? 0) }}
          </div>
          <div class="muted">Total Users</div>
        </div>
        <div class="card" style="text-align:center;">
          <div style="font-size:24px; font-weight:700; color:#10b981; margin-bottom:4px;">
            {{ ($stats['today']['shops'] ?? 0) + ($stats['last_15_days']['shops'] ?? 0) }}
          </div>
          <div class="muted">Total Shops</div>
        </div>
        <div class="card" style="text-align:center;">
          <div style="font-size:24px; font-weight:700; color:#f59e0b; margin-bottom:4px;">
            {{ ($stats['today']['stamps'] ?? 0) + ($stats['last_15_days']['stamps'] ?? 0) }}
          </div>
          <div class="muted">Total Stamps</div>
        </div>
        <div class="card" style="text-align:center;">
          <div style="font-size:24px; font-weight:700; color:#ef4444; margin-bottom:4px;">
            {{ ($stats['today']['redemptions'] ?? 0) + ($stats['last_15_days']['redemptions'] ?? 0) }}
          </div>
          <div class="muted">Total Redemptions</div>
        </div>
      </div>

      <div class="card" style="margin-bottom:16px;">
        <h2 style="margin:0 0 8px; font-size:16px;">Send notification to shop owners</h2>
        <form method="POST" action="/notify/owners" style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
          @csrf
          <div style="flex:1 1 240px;">
            <label class="muted">Title</label>
            <input name="title" required maxlength="120" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;" />
          </div>
          <div style="flex:2 1 360px;">
            <label class="muted">Description</label>
            <input name="description" required maxlength="500" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;" />
          </div>
          <button type="submit" style="padding:10px 14px; border:none; border-radius:8px; background:#111827; color:#fff; cursor:pointer;">Send to owners</button>
        </form>
      </div>

      <div class="card" style="margin-bottom:16px;">
        <h2 style="margin:0 0 8px; font-size:16px;">Send notification to users</h2>
        <form method="POST" action="/notify/users" style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
          @csrf
          <div style="flex:1 1 240px;">
            <label class="muted">Title</label>
            <input name="title" required maxlength="120" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;" />
          </div>
          <div style="flex:2 1 360px;">
            <label class="muted">Description</label>
            <input name="description" required maxlength="500" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;" />
          </div>
          <button type="submit" style="padding:10px 14px; border:none; border-radius:8px; background:#111827; color:#fff; cursor:pointer;">Send to users</button>
        </form>
      </div>

      <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0 0 12px;">Received Advertisements</h1>
      <div class="toolbar">
        <a href="/" class="{{ $status ? '' : 'active' }}">All</a>
        <a href="/?status=pending" class="{{ $status === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="/?status=approved" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
        <a href="/?status=rejected" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="/?status=sent" class="{{ $status === 'sent' ? 'active' : '' }}">Sent</a>
        <a href="/?status=failed" class="{{ $status === 'failed' ? 'active' : '' }}">Failed</a>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <h3 style="margin:0 0 16px; font-size:18px; font-weight:600; color:#111827;">📊 Dashboard Insights</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:16px; margin-bottom:16px;">
          <!-- Today's Statistics -->
          <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; padding:20px; border-radius:12px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:500;">📅 Today</h4>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Users:</span>
              <span style="font-weight:600;">{{ $stats['today']['users'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Shops:</span>
              <span style="font-weight:600;">{{ $stats['today']['shops'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Stamps:</span>
              <span style="font-weight:600;">{{ $stats['today']['stamps'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:12px;">Redemptions:</span>
              <span style="font-weight:600;">{{ $stats['today']['redemptions'] ?? 0 }}</span>
            </div>
          </div>

          <!-- Yesterday Comparison -->
          <div style="background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color:#fff; padding:20px; border-radius:12px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:500;">📊 vs Yesterday</h4>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Users:</span>
              <span style="font-weight:600; color:{{ ($stats['yesterday_comparison']['users'] ?? 0) >= 0 ? '#10b981' : '#ef4444' }}">
                {{ ($stats['yesterday_comparison']['users'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['yesterday_comparison']['users'] ?? 0 }}
              </span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Shops:</span>
              <span style="font-weight:600; color:{{ ($stats['yesterday_comparison']['shops'] ?? 0) >= 0 ? '#10b981' : '#ef4444' }}">
                {{ ($stats['yesterday_comparison']['shops'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['yesterday_comparison']['shops'] ?? 0 }}
              </span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Stamps:</span>
              <span style="font-weight:600; color:{{ ($stats['yesterday_comparison']['stamps'] ?? 0) >= 0 ? '#10b981' : '#ef4444' }}">
                {{ ($stats['yesterday_comparison']['stamps'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['yesterday_comparison']['stamps'] ?? 0 }}
              </span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:12px;">Redemptions:</span>
              <span style="font-weight:600; color:{{ ($stats['yesterday_comparison']['redemptions'] ?? 0) >= 0 ? '#10b981' : '#ef4444' }}">
                {{ ($stats['yesterday_comparison']['redemptions'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['yesterday_comparison']['redemptions'] ?? 0 }}
              </span>
            </div>
          </div>

          <!-- Last 15 Days -->
          <div style="background:linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color:#fff; padding:20px; border-radius:12px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:500;">📈 Last 15 Days</h4>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Users:</span>
              <span style="font-weight:600;">{{ $stats['last_15_days']['users'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Shops:</span>
              <span style="font-weight:600;">{{ $stats['last_15_days']['shops'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Stamps:</span>
              <span style="font-weight:600;">{{ $stats['last_15_days']['stamps'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:12px;">Redemptions:</span>
              <span style="font-weight:600;">{{ $stats['last_15_days']['redemptions'] ?? 0 }}</span>
            </div>
          </div>

          <!-- Last Month -->
          <div style="background:linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color:#fff; padding:20px; border-radius:12px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:500;">📊 Last Month</h4>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Users:</span>
              <span style="font-weight:600;">{{ $stats['last_month']['users'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Shops:</span>
              <span style="font-weight:600;">{{ $stats['last_month']['shops'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:12px;">Stamps:</span>
              <span style="font-weight:600;">{{ $stats['last_month']['stamps'] ?? 0 }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:12px;">Redemptions:</span>
              <span style="font-weight:600;">{{ $stats['last_month']['redemptions'] ?? 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Due Insights -->
        <div style="background:linear-gradient(135deg, #fa709a 0%, #fee140 100%); color:#fff; padding:20px; border-radius:12px; margin-bottom:16px;">
          <h4 style="margin:0 0 12px; font-size:16px; font-weight:600;">💰 Payment Due Insights</h4>
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <div>
              <div style="font-size:24px; font-weight:700; margin-bottom:4px;">{{ $stats['payment_due']['total_amount'] ?? 0 }} DA</div>
              <div style="font-size:12px;">Total Due</div>
            </div>
            <div>
              <div style="font-size:24px; font-weight:700; margin-bottom:4px;">{{ $stats['payment_due']['pending_redemptions'] ?? 0 }}</div>
              <div style="font-size:12px;">Pending Redemptions</div>
            </div>
            <div>
              <div style="font-size:24px; font-weight:700; margin-bottom:4px;">{{ $stats['payment_due']['affected_shops'] ?? 0 }}</div>
              <div style="font-size:12px;">Shops Affected</div>
            </div>
            <div>
              <div style="font-size:24px; font-weight:700; margin-bottom:4px;">{{ $stats['payment_due']['oldest_due_days'] ?? 0 }}</div>
              <div style="font-size:12px;">Days Since Oldest</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Shop</th>
              <th>Owner</th>
              <th>Title</th>
              <th>Description</th>
              <th>Status</th>
              <th>Targets</th>
              <th>Delivered</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($ads as $ad)
              <tr>
                <td>#{{ $ad->id }}</td>
                <td class="muted">{{ optional($ad->shop)->name ?? 'Shop #'.$ad->shop_id }}</td>
                <td class="muted">User #{{ $ad->owner_user_id }}</td>
                <td class="title">{{ $ad->title }}</td>
                <td class="muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  {{ Str::limit($ad->description, 50) }}
                </td>
                <td>
                  <span class="badge badge-{{ $ad->status }}">{{ ucfirst($ad->status) }}</span>
                </td>
                <td>{{ $ad->target_count }}</td>
                <td>{{ $ad->delivered_count }}</td>
                <td class="muted">{{ $ad->created_at->diffForHumans() }}</td>
                <td>
                  @if($ad->status === 'pending')
                    <form method="POST" action="/ads/{{ $ad->id }}/approve" style="display:inline-block; margin-right:6px;">
                      @csrf
                      <button type="submit" style="padding:6px 10px; border:none; border-radius:8px; background:#111827; color:#fff; cursor:pointer;">Approve</button>
                    </form>
                    <form method="POST" action="/ads/{{ $ad->id }}/reject" style="display:inline-block;">
                      @csrf
                      <input type="hidden" name="reason" value="Rejected from web dashboard">
                      <button type="submit" style="padding:6px 10px; border:none; border-radius:8px; background:#f43f5e; color:#fff; cursor:pointer;">Reject</button>
                    </form>
                  @else
                    <span class="muted">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="muted">No advertisements yet.</td></tr>
            @endforelse
          </tbody>
        </table>
        <div style="margin-top:12px;">
          {{ $ads->links() }}
        </div>
      </div>
    </div>
  </body>
</html>


