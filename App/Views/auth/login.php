<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Beneficiary Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-dark) 0%, var(--bg-card) 100%);
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
        .login-card .card-header {
            text-align: center;
            padding: 2rem 1.5rem;
        }
        .login-card .card-header i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .login-card .card-header h3 {
            color: var(--text-primary);
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="card-header">
                <i class="fas fa-handshake"></i>
                <h3>Beneficiary Manager</h3>
                <p class="text-secondary mb-0">Sign in to your account</p>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/auth/authenticate">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <hr class="my-3">

                <p class="text-center text-secondary mb-0">
                    Don't have an account? <a href="/auth/register" class="text-primary">Register here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/theme.js"></script>
</body>
</html>