<!DOCTYPE html>
<html>
<head>
    <title>DBStan Report</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .issue-card {
            border-left: 4px solid #f59e0b;
            transition: all 0.2s ease;
        }

        .issue-card:hover {
            background-color: #fffdf5;
        }

        .note-box {
            background: #f8fafc;
            border-left: 4px solid #0d6efd;
            padding: 7px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .card-body {
            padding: 7px;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>

<body class="p-4 bg-light">

<div class="container">

    <h2 class="mb-4 fw-bold">⚡ DBStan Analysis</h2>

    @if(count($groupedIssues) > 0)
        <div class="row">
            <!-- Left Side Vertical Tabs -->
            <div class="col-md-3">
                <ul class="nav nav-pills flex-column"
                    id="issueTabs"
                    role="tablist">

                    @foreach($groupedIssues as $category => $items)
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }} text-start w-100"
                                    data-bs-toggle="pill"
                                    data-bs-target="#content-{{ $loop->index }}"
                                    type="button">

                                {{ ucfirst(str_replace('_',' ',$category)) }}
                                <span class="badge bg-danger float-end">
                                    {{ count($items) }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Right Side Content -->
            <div class="col-md-9">
                <div class="tab-content border bg-white p-3 rounded shadow-sm">

                    @foreach($groupedIssues as $category => $checks)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="content-{{ $loop->index }}">

                            @foreach($checks as $checkName => $messages)

                                <div class="card mb-3 shadow-sm">

                                    <!-- Collapsible Header -->
                                    <div class="card-header bg-white">
                                        <button class="btn btn-link text-decoration-none w-100 text-start d-flex justify-content-between align-items-center"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse-{{ $category }}-{{ $loop->index }}">

                                            <span>
                                                <strong>
                                                    {{ ucfirst(str_replace('_',' ', $checkName)) }}
                                                </strong>
                                                    ({{ count($messages) }})
                                            </span>

                                            <i class="bi bi-chevron-down toggle-icon"></i>
                                        </button>
                                    </div>

                                    <!-- Collapsible Body -->
                                    <div id="collapse-{{ $category }}-{{ $loop->index }}"
                                        class="collapse {{ $loop->first ? 'show' : '' }}">

                                        <div class="card-body bg-light">

                                            @foreach($messages as $message)
                                                <div class="mb-2">
                                                    <pre class="mb-0 text-muted">{{ $message }}</pre>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>

                                </div>

                            @endforeach

                        </div>
                    @endforeach

                </div>
            </div>

        </div>

    @else
        <div class="alert alert-success shadow-sm">
            🎉 No issues found. Great job!
        </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>