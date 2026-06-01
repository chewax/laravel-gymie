<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name') }} — Membership card</title>
    <style>
        :root { --brand: #157573; --brand-dark: #0e5c5a; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(160deg, var(--brand) 0%, var(--brand-dark) 100%); color: #fff; padding: 24px;
        }
        .card {
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            border-radius: 20px; padding: 32px 24px; max-width: 380px; width: 100%; text-align: center;
            backdrop-filter: blur(8px);
        }
        .brand { font-size: 14px; letter-spacing: .15em; text-transform: uppercase; opacity: .8; }
        h1 { font-size: 24px; margin: 8px 0 4px; }
        .sub { opacity: .85; font-size: 14px; margin-bottom: 28px; }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 16px; border-radius: 14px; font-size: 16px; font-weight: 600;
            text-decoration: none; margin-bottom: 12px;
        }
        .btn:active { opacity: .8; }
        .btn-apple { background: #000; color: #fff; }
        .btn-google { background: #fff; color: #3c4043; }
        .hint { font-size: 12px; opacity: .7; margin-top: 16px; line-height: 1.5; }
        svg { vertical-align: middle; flex: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">{{ config('app.name') }}</div>
        <h1>Hi {{ $member->name }} 👋</h1>
        <p class="sub">Add your membership card to your phone’s wallet.</p>

        @foreach (($isAndroid ? ['google', 'apple'] : ['apple', 'google']) as $platform)
            @if ($platform === 'apple' && $apple)
                <a class="btn btn-apple" href="{{ route('wallet.card.apple', $token) }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M16.36 1.43c.04 1.06-.37 2.09-1.05 2.86-.71.79-1.86 1.4-2.96 1.31-.05-1.04.43-2.1 1.07-2.78.72-.78 1.95-1.36 2.94-1.39zM20.5 17.13c-.55 1.27-.82 1.83-1.53 2.95-.99 1.56-2.38 3.51-4.11 3.52-1.53.02-1.93-1-4.01-.99-2.08.01-2.51 1-4.05.98-1.73-.02-3.05-1.78-4.04-3.34-2.78-4.27-3.08-9.31-1.36-11.99 1-1.51 2.56-2.47 4.04-2.47 1.51 0 2.46.99 3.71.99 1.21 0 1.95-.99 3.7-.99 1.32 0 2.72.72 3.72 1.96-3.27 1.79-2.74 6.46.33 7.94z"/></svg>
                    Add to Apple Wallet
                </a>
            @elseif ($platform === 'google' && $google)
                <a class="btn btn-google" href="{{ route('wallet.card.google', $token) }}">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M21 12.2c0-.6-.05-1.2-.16-1.7H12v3.4h5.05a4.3 4.3 0 0 1-1.87 2.82v2.3h3.02C19.95 17.36 21 15 21 12.2z"/><path fill="#34A853" d="M12 21.5c2.43 0 4.47-.8 5.96-2.18l-3.02-2.3c-.84.56-1.92.9-2.94.9-2.26 0-4.18-1.53-4.86-3.58H3.98v2.37A9 9 0 0 0 12 21.5z"/><path fill="#FBBC05" d="M7.14 14.34a5.4 5.4 0 0 1 0-3.45V8.52H3.98a9 9 0 0 0 0 8.19l3.16-2.37z"/><path fill="#EA4335" d="M12 6.5c1.32 0 2.5.46 3.44 1.35l2.58-2.58A9 9 0 0 0 3.98 8.52l3.16 2.37C7.82 8.84 9.74 6.5 12 6.5z"/></svg>
                    Add to Google Wallet
                </a>
            @endif
        @endforeach

        <p class="hint">
            At the gym, just open your wallet and show the card’s QR to the check-in scanner.
        </p>
    </div>
</body>
</html>
