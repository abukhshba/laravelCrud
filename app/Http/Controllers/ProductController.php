<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\product;
use App\Http\Requests\ProductStoreRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //all products
        $products = Product::all();
        return response()->json([
            'products' => $products
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        try{
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();

            //create product
            product::create([
                'name'=>$request->name,
                'image'=>$imageName,
                'description'=>$request->description
            ]);

            //save Image in storage
            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            //return json responce
            return response()->json([
                'message' => "product successfully created"
            ]);

        } catch (\Exception $e) {
            //return json responce
            return response()->json([
                'message'=> $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         //product details
         $product = Product::find($id);
         if(!$product){
             return response()->json([
                 'message' => 'product not found'
             ], 404);
         }
         //return json responce
         return response()->json([
             'product' => $product
         ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductStoreRequest $request, $id)
    {
        try{
            //find product
            $product = Product::find($id);
            if(!$product){
                return response()->json([
                    'message' => 'product not found'
                ], 404);
            }

            $product->name = $request->name;
            $product->description = $request->description;

            if($request->image){
                $storage = Storage::disk('public');
                //delete old image
                if ($storage->exists($product->image))
                    $storage->delete($product->image);

                //image name
                $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
                $product->image = $imageName;

                //save image in public folder
                $storage->put($imageName, file_get_contents($request->image));
            }
            $product->save();

            return response()->json([
                'message' => 'product successfully updated'
            ], 200);


        } catch(\Exception $e){
            //return json responce
            return response()->json([
                'message' => 'somthing went wrong!'
            ], 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'product not found'
            ], 404);
        }

        $storage = Storage::disk('public');
        //delete old image
        if ($storage->exists($product->image))
            $storage->delete($product->image);

        //delete product
        $product->delete();
        
        //return json message
        return response()->json([
            'message' => 'product successfully deleted'
        ], 200);
    }
}