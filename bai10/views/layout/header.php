<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 10 - Mini MVC</title>
  <style>
    * { box-sizing: border-box; }
    body { margin:0; padding:24px; font-family: Arial; background:#f4f6f8; color:#111; }
    .wrap { max-width: 1000px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; }
    h1 { margin:0 0 12px; font-size:18px; }
    a { color: inherit; text-decoration:none; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; margin: 10px 0 12px; }
    .btn { display:inline-block; padding:8px 12px; border-radius:10px; border:1px solid #d1d5db; background:#fff; cursor:pointer; font-size:14px; }
    .btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
    .btn-danger { background:#ef4444; border-color:#ef4444; color:#fff; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #e5e7eb; padding:10px; text-align:center; vertical-align:middle; }
    th { background:#f9fafb; }
    label { display:block; font-size:13px; margin:0 0 6px; color:#374151; }
    input, textarea { width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:10px; outline:none; font-size:14px; }
    textarea { min-height: 110px; resize: vertical; }
    .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media (max-width: 800px) { .grid{ grid-template-columns:1fr; } }
    .full { grid-column: 1 / -1; }
    .invalid { border-color:#ef4444 !important; }
    .error { margin-top:6px; font-size:13px; color:#ef4444; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
