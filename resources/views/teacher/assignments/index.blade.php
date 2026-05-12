@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>My Assignments</h4>
            <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary">Create Assignment</a>
        </div>
        <div class="card-body">
            @if($assignments->count())
                <table class="table">
                    <thead>
                        <tr><th>Title</th><th>Class</th><th>Due Date</th><th>Actions</th</tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->title }}</td>
                            <td>{{ $assignment->class->name ?? 'N/A' }}</td>
                            <td>{{ $assignment->due_date }}</td>
                            <td>
                                <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('teacher.assignments.destroy', $assignment) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $assignments->links() }}
            @else
                <p>No assignments yet. <a href="{{ route('teacher.assignments.create') }}">Create one</a></p>
            @endif
        </div>
    </div>
</div>
@endsection
