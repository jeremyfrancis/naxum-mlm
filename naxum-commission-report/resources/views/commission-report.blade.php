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
            background-color: rgba(0, 0, 0, 0.03);
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Commission Report</h1>
        
        <div class="card filters">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('commission-report') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="distributor" class="form-label">Distributor</label>
                            <input type="text" class="form-control" id="distributor" name="distributor" 
                                placeholder="Distributor ID, First Name, or Last Name" value="{{ $distributor ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $end_date ?? '' }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('commission-report') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Results</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $row)
                                <tr>
                                    <td>{{ $row['invoice'] }}</td>
                                    <td>{{ $row['purchaser'] }}</td>
                                    <td>{{ $row['distributor'] }}</td>
                                    <td>{{ $row['referred_distributors'] }}</td>
                                    <td>{{ $row['order_date'] }}</td>
                                    <td>{{ $row['percentage'] }}%</td>
                                    <td>${{ number_format($row['order_total'], 2) }}</td>
                                    <td>${{ number_format($row['commission'], 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm view-items-btn" 
                                                data-bs-toggle="modal" data-bs-target="#itemsModal" 
                                                data-items="{{ json_encode($row['items']) }}"
                                                data-invoice="{{ $row['invoice'] }}">
                                            View Items
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $pagination->firstItem() ?? 0 }} to {{ $pagination->lastItem() ?? 0 }} of {{ $pagination->total() }} entries
                    </div>
                    <div>
                        {{ $pagination->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Modal -->
    <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemsModalLabel">Order Items for Invoice #</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTableBody">
                            <!-- Items will be injected here by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var itemsModal = document.getElementById('itemsModal');
            itemsModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var items = JSON.parse(button.getAttribute('data-items'));
                var invoice = button.getAttribute('data-invoice');

                var modalTitle = itemsModal.querySelector('.modal-title');
                var modalTableBody = itemsModal.querySelector('#modalItemsTableBody');

                modalTitle.textContent = 'Order Items for Invoice #' + invoice;
                modalTableBody.innerHTML = ''; // Clear previous items

                items.forEach(function (item) {
                    var product = item.product || { name: 'N/A', sku: 'N/A', price: 0 };
                    var row = `<tr>
                                <td>${product.sku}</td>
                                <td>${product.name}</td>
                                <td>$${parseFloat(product.price).toFixed(2)}</td>
                                <td>${item.quantity}</td>
                                <td>$${(product.price * item.quantity).toFixed(2)}</td>
                            </tr>`;
                    modalTableBody.innerHTML += row;
                });
            });
        });
    </script>
</body>
</html> 