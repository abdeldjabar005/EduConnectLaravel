<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="admin-info">
    <h2 >Admin Dashboard</h2>
    <div class="admin-info-right">
        <span>{{ Auth::user()->first_name }}</span>
        <span>{{ Auth::user()->last_name }}</span>
        <div class="dropdown">
            <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Admin Image" class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">{{ "settings" }}</a>
                <a href="#">{{ "help" }}</a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<div class="parent-container">
  <div class="stats-container">
    <div class="stat-card">
        <i class="fas fa-school icon" style="color: #707EFF;"></i> <!-- Icon for schools -->
        <h3>Schools</h3>
        <p>{{ $schoolsCount }}</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-chalkboard-teacher icon" style="color: #707EFF;"></i> <!-- Icon for classes -->
        <h3>Classes</h3>
        <p>{{ $classesCount }}</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-graduate icon" style="color: #707EFF;"></i> <!-- Icon for teachers -->
        <h3>Teachers</h3>
        <p>{{ $teachersCount }}</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-users icon" style="color: #707EFF;"></i> <!-- Icon for users -->
        <h3>Users</h3>
        <p>{{ $usersCount }}</p>
    </div>
</div>
    <div class="container">

<h2 class="school">Schools with Verification Requests</h2>
<table class="school-requests">
        <thead>
            <tr>
                <th>School Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Document</th>
                <th class="actions-header">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($schools as $school)
            @foreach ($school->verificationRequests as $request)
                <tr id="school-{{ $school->id }}">
                        <td>{{ $school->name }}</td>
                        <td>{{ $request->email }}</td>
                        <td>{{ $request->phone_number }}</td>
                        <td><a href="{{ Storage::url($request->document_path) }}">View Document</a></td>
                        <td>
                            <div class="button-container">
                                <form action="{{ route('admin.verifySchool', $school->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="verify">Verify</button>
                                </form>
                                <form action="{{ route('admin.rejectSchool', $school->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="reject">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
</div>
</body>
</html>
