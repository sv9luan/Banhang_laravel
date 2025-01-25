@extends('layouts.app')

@section('content')
    <main class="pt-90">
        <section class="my-account container">
            <h2 class="page-title">Edit Address</h2>
            <div class="row">
                <div class="col-lg-3">
                    @include('user.account-nav') <!-- Navigation bên trái -->
                </div>
                <div class="col-lg-9">
                    <div class="page-content my-account__address">
                        <form action="{{ route('user.account.address.update', $address->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $address->name) }}">
                                        <label for="name">Full Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', $address->phone) }}">
                                        <label for="phone">Phone Number *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="zip"
                                            value="{{ old('zip', $address->zip) }}">
                                        <label for="zip">Pincode *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating mt-3 mb-3">
                                        <input type="text" class="form-control" name="state"
                                            value="{{ old('state', $address->state) }}">
                                        <label for="state">State *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="city"
                                            value="{{ old('city', $address->city) }}">
                                        <label for="city">Town / City *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="address"
                                            value="{{ old('address', $address->address) }}">
                                        <label for="address">House no, Building Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="locality"
                                            value="{{ old('locality', $address->locality) }}">
                                        <label for="locality">Road Name, Area, Colony *</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" name="landmark"
                                            value="{{ old('landmark', $address->landmark) }}">
                                        <label for="landmark">Landmark *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="isdefault"
                                            name="isdefault" {{ $address->isdefault ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isdefault">
                                            Make as Default address
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-success">Update Address</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
