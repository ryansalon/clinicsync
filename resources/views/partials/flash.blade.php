@if (session('status'))
    <div class="animate-fade-in mb-4" data-flash>
        <x-alert type="success" :dismiss="true">
            {{ session('status') }}
        </x-alert>
    </div>
@endif

@if ($errors->any())
    <div class="animate-fade-in mb-4" data-flash>
        <x-alert type="error" title="Please review the following:">
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    </div>
@endif
