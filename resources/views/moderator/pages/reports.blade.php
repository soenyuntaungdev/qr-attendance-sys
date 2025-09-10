@extends('moderator.layouts.layout')

@section('title', 'Reports')

@section('main-content')
<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Generate Attendance Reports</h5>
                </div>
                <div class="card-body">
                    <form id="report-form" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="report-type" class="form-label">Report Type</label>
                            <select class="form-select" id="report-type">
                                <option value="daily">Daily Report</option>
                                <option value="weekly">Weekly Report</option>
                                <option value="monthly">Monthly Report</option>
                                <option value="custom">Custom Date Range</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="report-usertype" class="form-label">User Type</label>
                            <select class="form-select" id="report-usertype">
                                <option value="all">All Users</option>
                                <option value="students">Students Only</option>
                                <option value="teachers">Teachers Only</option>
                                <option value="staff">Staff Only</option>
                                <option value="visitors">Visitors Only</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="report-location" class="form-label">Location</label>
                            <select class="form-select" id="report-location">
                                <option value="all">All Locations</option>
                                <option value="main-entrance">Main Entrance</option>
                                <option value="classroom-1">Classroom 1</option>
                                <option value="library">Library</option>
                                <option value="admin-office">Admin Office</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="report-format" class="form-label">Export Format</label>
                            <select class="form-select" id="report-format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 date-range d-none" id="custom-date-range">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="report-start-date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="report-start-date">
                                </div>
                                <div class="col-md-6">
                                    <label for="report-end-date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="report-end-date">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-export me-1"></i> Generate Report
                            </button>
                            <button type="reset" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Preview Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Report Preview</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" id="btn-print-report">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <button class="btn btn-sm btn-primary" id="btn-download-report">
                            <i class="fas fa-download me-1"></i> Download
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-summary mb-4 row">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center">
                                <div class="card-body">
                                    <h6>Total Records</h6>
                                    <h2 id="total-records">385</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center">
                                <div class="card-body">
                                    <h6>Attendance Rate</h6>
                                    <h2 id="attendance-rate">92.5%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center">
                                <div class="card-body">
                                    <h6>Avg. Duration</h6>
                                    <h2 id="avg-duration">7.2h</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center">
                                <div class="card-body">
                                    <h6>Late Entries</h6>
                                    <h2 id="late-entries">42</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-chart mb-4">
                        <canvas id="report-chart" height="200"></canvas>
                    </div>

                    <div class="report-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th>Entry Time</th>
                                        <th>Exit Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="report-table-body">
                                    <!-- Dynamically loaded -->
                                </tbody>
                            </table>
                        </div>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('moderator/js/reports.js') }}"></script>
@endpush
