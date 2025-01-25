@extends('layouts.app')
@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Addresses</h2>
            <div class="row">
                <div class="col-lg-3">
                    @include('user.account-nav')
                </div>
                <div class="col-lg-9">
                    <div class="page-content my-account__address">
                        <div class="row">
                            <div class="col-6">
                                <p class="notice">The following addresses will be used on the checkout page by default.</p>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('user.acc_add_add') }}" class="btn btn-sm btn-info">Add New</a>
                            </div>
                        </div>
                        <div class="my-account__address-list row">
                            <h5>Shipping Address</h5>
                            @foreach ($address as $addre)
                                <div class="my-account__address-item col-md-6">
                                    <div class="my-account__address-item__title">
                                        <h5>
                                            {{ $addre->name }}
                                            @if ($addre->isdefault)
                                                <span style="color:red">Default Address</span>
                                            @else
                                                <i class="fa fa-check-circle text-success"></i>
                                            @endif
                                        </h5>
                                        <a href="{{ route('user.account.address.edit', $addre->id) }}">Edit</a>
                                    </div>
                                    <div class="my-account__address-item__detail">
                                        <p><strong>Address:</strong> {{ $addre->address }}</p>
                                        <p><strong>Landmark:</strong> {{ $addre->landmark }}</p>
                                        <p><strong>City:</strong> {{ $addre->city }}</p>
                                        <p><strong>State:</strong> {{ $addre->state }}</p>
                                        <p><strong>Country:</strong> {{ $addre->country }}</p>
                                        <p><strong>Pincode:</strong> {{ $addre->zip }}</p>
                                        <p><strong>Phone:</strong> {{ $addre->phone }}</p>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
