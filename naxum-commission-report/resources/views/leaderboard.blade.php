<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Distributors Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Top 200 Distributors</h1>
        <p class="text-muted">Showing distributors ranked by total sales</p>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Top</th>
                                <th scope="col">Distributor's Name</th>
                                <th scope="col">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distributors as $distributor)
                                <tr>
                                    <th scope="row">{{ $distributor->rank }}</th>
                                    <td>{{ $distributor->first_name }} {{ $distributor->last_name }}</td>
                                    <td>${{ number_format($distributor->total_sales, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $distributors->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 