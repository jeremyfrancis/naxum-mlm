<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .filters {
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="mb-0">Commission Report</h1>
            </div>
            <div class="card-body">
                <div class="filters">
                    <form method="GET" action="{{ route('commission-report') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="distributor" class="form-label">Distributor (ID, First Name, or Last Name)</label>
                            <input type="text" class="form-control" id="distributor" name="distributor" value="{{ $request->distributor }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $request->date_from }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $request->date_to }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('commission-report') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Purchaser</th>
                                <th>Distributor</th>
                                <th>Referred Distributors</th>
                                <th>Order Date</th>
                                <th>Percentage</th>
                                <th>Order Total</th>
                                <th>Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $orderData)
                                <tr>
                                    <td>{{ $orderData['invoice'] }}</td>
                                    <td>{{ $orderData['purchaser'] }}</td>
                                    <td>{{ $orderData['distributor'] }}</td>
                                    <td>{{ $orderData['referred_distributors'] }}</td>
                                    <td>{{ $orderData['order_date'] }}</td>
                                    <td>{{ $orderData['percentage'] }}%</td>
                                    <td>${{ number_format($orderData['order_total'], 2) }}</td>
                                    <td>${{ number_format($orderData['commission'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No orders found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 