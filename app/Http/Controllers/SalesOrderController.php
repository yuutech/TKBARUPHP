<?php
/**
 * Created by PhpStorm.
 * User: Sugito
 * Date: 9/26/2016
 * Time: 6:55 PM
 */

namespace App\Http\Controllers;

use App\Model\Customer;
use App\Model\Item;
use App\Model\Lookup;
use App\Model\Product;
use App\Model\ProductUnit;
use App\Model\PurchaseOrder;
use App\Model\SalesOrder;
use App\Model\Stock;
use App\Model\VendorTrucking;
use App\Model\Warehouse;
use App\Util\SOCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SalesOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        Log::info('SalesOrderController@create');

        $customerDDL = Customer::all(['id', 'name']);
        $warehouseDDL = Warehouse::all(['id', 'name']);
        $vendorTruckingDDL = VendorTrucking::all(['id', 'name']);
        $productDDL = Product::with('productUnits.unit')->get();
        $stocksDDL = Stock::with('product.productUnits.unit')->orderBy('product_id', 'asc')
            ->orderBy('created_at', 'asc')->where('current_quantity', '>', 0)->get();
        $soTypeDDL = Lookup::where('category', '=', 'SOTYPE')->get()->pluck('description', 'code');
        $customerTypeDDL = Lookup::where('category', '=', 'CUSTOMERTYPE')->get()->pluck('description', 'code');
        $soCode = SOCodeGenerator::generateSOCode();
        $soStatusDraft = Lookup::where('category', '=', 'SOSTATUS')->get(['description', 'code'])->where('code', '=',
            'SOSTATUS.D');


        return view('sales_order.create', compact('soTypeDDL', 'customerTypeDDL', 'warehouseDDL',
            'productDDL', 'stocksDDL', 'vendorTruckingDDL', 'customerDDL'
            , 'soCode', 'soStatusDraft'));
    }

    public function store(Request $request)
    {
        Log::info('SalesOrderController@store');

        for($i = 0; $i < count($request->input('so_code')); $i++){
            $params = [
                'customer_type' => $request->input("customer_type.$i"),
                'customer_id' => empty($request->input("customer_id.$i")) ? 0 :$request->input("customer_id.$i"),
                'walk_in_cust' => $request->input("walk_in_customer.$i"),
                'walk_in_cust_details' => $request->input("walk_in_customer_details.$i"),
                'code' => $request->input("so_code.$i"),
                'so_type' => $request->input("sales_type.$i"),
                'so_created' => date('Y-m-d', strtotime($request->input("so_created.$i"))),
                'shipping_date' => date('Y-m-d', strtotime($request->input("shipping_date.$i"))),
                'status' => Lookup::whereCode('SOSTATUS.WD')->first()->code,
                'vendor_trucking_id' => empty($request->input("vendor_trucking_id.$i")) ? 0 : $request->input("vendor_trucking_id.$i"),
                'warehouse_id' => $request->input("warehouse_id.$i"),
                'remarks' => $request->input("remarks.$i"),
                'store_id' => Auth::user()->store_id
            ];

            $so = SalesOrder::create($params);

            for ($j = 0; $j < count($request->input("so_$i"."_product_id")); $j++) {
                $item = new Item();
                $item->product_id = $request->input("so_$i"."_product_id.$j");
                $item->stock_id = $request->input("so_$i"."_stock_id.$j");
                $item->store_id = Auth::user()->store_id;
                $item->selected_unit_id = $request->input("so_$i"."_selected_unit_id.$j");
                $item->base_unit_id = $request->input("so_$i"."_base_unit_id.$j");
                $item->conversion_value = ProductUnit::where([
                    'product_id' => $item->product_id,
                    'unit_id' => $item->selected_unit_id
                ])->first()->conversion_value;
                $item->quantity = $request->input("so_$i"."_quantity.$j");
                $item->price = $request->input("so_$i"."_price.$j");
                $item->to_base_quantity = $item->quantity * $item->conversion_value;

                $so->items()->save($item);
            }
        }

        return redirect(route('db'));
    }

    public function index()
    {
        Log::info('SalesOrderController@index');

        $salesOrders = SalesOrder::with('customer')->whereIn('status', ['SOSTATUS.WA', 'SOSTATUS.WD'])->get();
        $soStatusDDL = Lookup::where('category', '=', 'SOSTATUS')->get()->pluck('description', 'code');

        return view('sales_order.index', compact('salesOrders', 'soStatusDDL'));
    }

    public function revise($id)
    {
        Log::info('SalesOrderController@revise');

        $currentSo = SalesOrder::with('items.product.productUnits.unit', 'customer.profiles.phoneNumbers.provider',
            'customer.bankAccounts.bank', 'vendorTrucking', 'warehouse')->find($id);
        $warehouseDDL = Warehouse::all(['id', 'name']);
        $vendorTruckingDDL = VendorTrucking::all(['id', 'name']);
        $productDDL = Product::with('productUnits.unit')->get();
        $stocksDDL = Stock::with('product.productUnits.unit')->orderBy('product_id', 'asc')
            ->orderBy('created_at', 'asc')->where('current_quantity', '>', 0)->get();

        return view('sales_order.revise', compact('currentSo', 'productDDL', 'warehouseDDL', 'vendorTruckingDDL', 'stocksDDL'));
    }

    public function saveRevision(Request $request, $id)
    {

    }

    public function payment($id)
    {

    }

    public function savePayment(Request $request, $id)
    {

    }

    public function delete(Request $request, $id)
    {
        $so = SalesOrder::find($id);

        $so->status = 'SOSTATUS.RJT';
        $so->save();

        return redirect(route('db.so.revise.index'));
    }
}