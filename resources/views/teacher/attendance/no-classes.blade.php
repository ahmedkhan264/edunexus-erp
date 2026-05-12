@extends('layouts.app')

@section('title', 'No Class Assignments')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-chalkboard-teacher fa-4x text-muted"></i>
                    </div>
                    <h3 class="card-title">No Class Assignments</h3>
                    <p class="card-text text-muted">
                        You haven't been assigned to any classes yet. Please contact the administrator to get class assignments.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('teacher.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
