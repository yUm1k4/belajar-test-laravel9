@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Products</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (auth()->user()->is_admin)
                            <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
                            <br><br>
                        @endif

                        <table class="table">
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Price (IDR)</th>
                                <th>Action</th>
                            </tr>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->price }}</td>
                                    <td>{{ $product->price_idr }}</td>
                                    <td>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No products found</td>
                                </tr>
                            @endforelse
                        </table>

                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection