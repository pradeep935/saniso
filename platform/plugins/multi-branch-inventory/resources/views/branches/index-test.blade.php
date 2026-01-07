@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div style="padding: 20px;">
    <h1>BRANCH PAGE TEST - {{ now() }}</h1>
    
    <p>Branches count: {{ $branches->count() }}</p>
    
    <ul>
        @foreach ($branches as $branch)
            <li>{{ $branch->name }} - {{ $branch->code }}</li>
        @endforeach
    </ul>
    
    <p>Debug check: View is rendering successfully!</p>
</div>
@endsection