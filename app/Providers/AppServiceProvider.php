<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Category;
use App\Logo;
use App\Product;
use App\Brand;
use App\Customer;
use App\Productimage;
use App\Location;
use App\News;
use App\Pagecategory;
use App\Socialmedia;
use DB;
use Session;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
            Schema::defaultStringLength(191);
            
            // main logo here
            $mainlogo=Logo::where('status',1)->orderBy('id','DESC')->limit(1,0)->get(); 
            view()->share(compact('mainlogo'));
            // mainlogo

            $categories = Category::where('status',1)->get();
            view()->share(compact('categories'));
            // category
            $hcategories = Category::where('status',1)->orderBy('id','ASC')->get();
            view()->share(compact('hcategories'));
            // category

            $sidebarmenu = Category::where('status',1)->limit(7)->get();
            view()->share(compact('sidebarmenu'));
            // sidebar
            $brands = Brand::where('status',1)->get();
            view()->share(compact('brands'));

            $productimage =Productimage::orderBy('id','DESC')
            ->get();
             view()->share(compact('productimage'));

             
             $shippingCharg=Location::get();
             view()->share(compact('shippingCharg'));

             $news = News::where('status',1)->limit(1)->orderBy('id','DESC')->get();
            view()->share(compact('news'));

             $footermenuleft = Pagecategory::where(['status'=>1,'menu_id'=>1])->get();
            view()->share(compact('footermenuleft'));

             $footermenuright = Pagecategory::where(['status'=>1,'menu_id'=>2])->get();
            view()->share(compact('footermenuright'));
            
            
             $socialicons = Socialmedia::where(['status'=>1])-> orderBy('id','DESC')->get();
            view()->share(compact('socialicons'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
