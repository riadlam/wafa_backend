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
      <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0 0 12px;">Received Advertisements</h1>
      <div class="toolbar">
        <a href="/" class="{{ $status ? '' : 'active' }}">All</a>
        <a href="/?status=pending" class="{{ $status === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="/?status=approved" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
        <a href="/?status=rejected" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="/?status=sent" class="{{ $status === 'sent' ? 'active' : '' }}">Sent</a>
        <a href="/?status=failed" class="{{ $status === 'failed' ? 'active' : '' }}">Failed</a>
      </div>
      <div class="card">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Shop</th>
              <th>Owner</th>
              <th>Title</th>
              <th>Status</th>
              <th>Targets</th>
              <th>Delivered</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($ads as $ad)
              <tr>
                <td>#{{ $ad->id }}</td>
                <td class="muted">{{ optional($ad->shop)->name ?? 'Shop #'.$ad->shop_id }}</td>
                <td class="muted">User #{{ $ad->owner_user_id }}</td>
                <td class="title">{{ $ad->title }}</td>
                <td>
                  <span class="badge badge-{{ $ad->status }}">{{ ucfirst($ad->status) }}</span>
                </td>
                <td>{{ $ad->target_count }}</td>
                <td>{{ $ad->delivered_count }}</td>
                <td class="muted">{{ $ad->created_at->diffForHumans() }}</td>
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


