<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        /// Fetch all products
        $products = DB::table('products')->latest()->paginate(5);

        //Fetch variants list from variants table

        $variants = DB::table('variants')->get();

        //
        $productVariants = DB::table('product_variants')
            ///->groupBy('variant') // don't know why this group by dont work, showing multiples..
            ->groupby('variant')->distinct()
            ->get();
        foreach( $variants as $variant ){
            $productVariantArr = [];
            foreach( $productVariants as $productVariant ){
                if( $productVariant->variant_id == $variant->id ) {
                    $productVariantArr[] = $productVariant;
                }
            }
            $variant->productVariants = $productVariantArr;
        }

        //var_dupm($variant);


        foreach($products as $product){
            $product_id = $product->id;

            $product_variant_prices = DB::table('product_variant_prices')
                ->where('product_id', $product->id )
                ->get();
            $product_variants = DB::table('product_variants')
                ->where('product_id', $product->id )
                ->get();

            // var_dump($product_variants);
            // var_dump($product_variant_prices);
            $productVariantPriceArr = [];
            foreach( $product_variant_prices as $product_variant_price ) {

                

                $variantText = '';
                foreach( $product_variants as $product_variant ) {
                    if( $product_variant->id = $product_variant_price->product_variant_one ){
                        $variantText .= $product_variant->variant . '/';
                    } else if ( $product_variant->id = $product_variant_price->product_variant_two ) {
                        $variantText .= $product_variant->variant . '/';
                    } else if ( $product_variant->id = $product_variant_price->product_variant_three ) {
                        $variantText .= $product_variant->variant;
                    }
                }
                //var_dump($product_variant_price);
                $product_variant_price->variantText = $variantText;
                $productVariantPriceArr[] = $product_variant_price;
            }


        }

        return view('products.index',compact('products','productVariantPriceArr','variants'));
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
