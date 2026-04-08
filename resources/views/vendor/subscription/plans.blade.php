@extends('vendor.layouts.app')

@section('title', 'Edit Staff Member - RentApp')
@section('page-title', 'Edit Staff Member')

@section('content')
<div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-extrabold text-center mb-10">Subscription Plans</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($plans as $plan)
            <div class="bg-white rounded-lg shadow-lg p-8 flex flex-col items-center border border-gray-200">

                <h3 class="text-xl font-semibold mb-2">{{ $plan->name }}</h3>

                <div class="text-3xl font-bold mb-2">
                    ₹{{ $plan->price }}
                    <span class="text-base text-gray-500">/ {{ $plan->billing_cycle }}</span>
                </div>

                <ul class="text-gray-700 mb-6 space-y-2">
                    @foreach($plan->features as $key => $value)
                        <li>✔ {{ ucfirst(str_replace('_',' ', $key)) }} : {{ $value }}</li>
                    @endforeach
                </ul>

                <button 
                    class="buy-btn mt-auto w-full bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
                    data-id="{{ $plan->id }}"
                    data-price="{{ $plan->price }}"
                >
                    Buy Now
                </button>

            </div>
            @endforeach
    </div>
</div>
@endsection
@section('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.querySelectorAll('.buy-btn').forEach(button => {

    button.addEventListener('click', async function () {

        let planId = this.dataset.id;

        // 🔥 Always fetch price from backend (secure)
        let response = await fetch('create-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                plan_id: planId
            })
        });

        let data = await response.json();

        var options = {
            "key": "{{ env('RAZORPAY_KEY') }}",
            "amount": data.amount,
            "currency": "INR",
            "name": "RentApp",
            "description": data.plan_name,
            "order_id": data.order_id,

            "handler": function (response) {

                fetch('verify-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...response,
                        plan_id: planId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert("Payment Successful ✅");
                    //location.reload();
                });
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    });

});
</script>
@endsection
