@if($errors->has($name))
    <div>
        @foreach($errors->get($name) as $error)
            <small class="help-block text-red mt-1 mb-0">{{ $error }}</small>
        @endforeach
    </div>
@endif
