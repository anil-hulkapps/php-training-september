@if(!empty(@$success) && (@$success > 0) || !empty(@$fail) && (@$fail > 0))
    <p>
        Your Avalara mapped products import has been completed.
    </p>
    <p>
        @if(!empty(@$success) && (@$success > 0))
            <p>Total successfully imported products are : {{ $success }}</p>
        @endif

        @if(!empty(@$fail) && (@$fail > 0))
            <p>Total failed to import products are : {{ $fail }}</p>
        @endif
    </p>
@else
    <p> You are trying to upload the sheet with wrong CSV file format or Please upload an atleast one product</p>
@endif
