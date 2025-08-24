<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\ProductService\Entities\ProductService;

class UpdateProductLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle($event)
    {
        if (module_is_active('Account')) {
            $request        = $event->request;
            $productservice = $event->productService;
            
            $productService                          = ProductService::find($productservice->id);
            $productService->sale_chartaccount_id    = $request['sale_chartaccount_id'] ?? 0;
            $productService->expense_chartaccount_id = $request['expense_chartaccount_id'] ?? 0;

            $productService->update();
        }
    }
}
