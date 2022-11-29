<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Http\Requests\ProductStore;
use App\Http\Requests\ProductUpdate;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index( Request $request )
    {
        $variants = Variant::orderBy('title', 'asc')->get();
        $productVariants = ProductVariant::orderBy('variant', 'asc')->get();
        foreach( $variants as $variant ){
            $productVariantArr = [];
            foreach( $productVariants as $productVariant ){
                if( $productVariant->variant_id == $variant->id ) {
                    $productVariantArr[] = $productVariant;
                }
            }
            $variant->productVariants = $productVariantArr;
        }
        $products = Product::all();
        $productCollection = [];
        foreach( $products as $product ) {
            $productVariantPrices = ProductVariantPrice::where('product_id', $product->id )->get();
            $productVariants = ProductVariant::where('product_id', $product->id )->get();
            $productVariantPriceArr = [];
            foreach( $productVariantPrices as $productVariantPrice ) {
                $variantText = '';
                foreach( $productVariants as $productVariant ) {
                    if( $productVariant->id = $productVariantPrice->product_variant_one ){
                        $variantText .= $productVariant->variant . '/';
                    } else if ( $productVariant->id = $productVariantPrice->product_variant_two ) {
                        $variantText .= $productVariant->variant . '/';
                    } else if ( $productVariant->id = $productVariantPrice->product_variant_three ) {
                        $variantText .= $productVariant->variant;
                    }
                }
                $productVariantPrice->variantText = $variantText;
                $productVariantPriceArr[] = $productVariantPrice;
            }
            $product->productVariantPrices = $productVariantPriceArr;
            $productCollection[] = $product;
        }

        $filteredProducts = [];
        $filterProductIds = [];

        $isFilter = false;

        if ($request->has('title') && $request['title'] != null) {
            $title = $request['title'];
            foreach( $productCollection as $product ){
                if( strpos($product->title, $title) ){
                    if( !in_array($product->id, $filterProductIds) ){
                        $filterProductIds[] = $product->id;
                        $filteredProducts[] = $product;
                    }
                    
                }
            }
            $isFilter = true;
        }

        if ($request->has('variant') && $request['variant'] != null) {
            $variant = $request['variant'];
            foreach( $productCollection as $product ){
                if( count( $product->productVariantPrices ) > 0 ) {
                    foreach( $product->productVariantPrices as $productVariantPrice ){
                        if( $productVariantPrice->product_variant_one == $variant || $productVariantPrice->product_variant_two == $variant || $productVariantPrice->product_variant_three == $variant ){
                            if( !in_array($product->id, $filterProductIds) ){
                                $filterProductIds[] = $product->id;
                                $filteredProducts[] = $product;
                            }
                            
                        }
                    }
                }
            }
            $isFilter = true;
        }
        
        if (($request->has('price_from') && $request['price_from'] != null) && ($request->has('price_to') && $request['price_to'] != null) ) {
            $price_from = $request['price_from'];
            $price_to = $request['price_to'];
            foreach( $productCollection as $product ){
                foreach( $product->productVariantPrices as $productVariantPrice ) {
                    if( $productVariantPrice->price >= $price_from && $productVariantPrice->price <= $price_to ) {
                        if( !in_array($product->id, $filterProductIds) ){
                            $filteredProducts[] = $product;
                            $filterProductIds[] = $product->id;
                        }
                    }
                }
            }
            $isFilter = true;
        }

        if ($request->has('date') && $request['date'] != null) {
            $date = strtotime($request['date']);
            foreach( $productCollection as $product ){
                $strtotime = strtotime($product->created_at);
                $created_at = date('Y-m-d', $strtotime);
                if( strtotime($created_at) == $date ){
                    if( !in_array($product->id, $filterProductIds) ){
                        $filterProductIds[] = $product->id;
                        $filteredProducts[] = $product;
                    }
                }
            }
            $isFilter = true;
        }
        
        if( $isFilter ) {
            $products = $this->paginate($filteredProducts);
        } else {
            $products = $this->paginate($productCollection);
        }

        return view('products.index', [
            'products' => $products,
            'variants' => $variants,
        ]);
    }

    public function paginate($items, $perPage = 1, $page = null)
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}

