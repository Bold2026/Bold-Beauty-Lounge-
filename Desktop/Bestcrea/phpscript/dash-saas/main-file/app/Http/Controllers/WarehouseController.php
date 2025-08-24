<?php

namespace App\Http\Controllers;

use App\DataTables\WarehouseDataTable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\Purchase;
use App\Events\CreateWarehouse;
use App\Events\DestroyWarehouse;
use App\Events\UpdateWarehouse;




class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(WarehouseDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('warehouse manage'))
        {
            return $dataTable->render('warehouses.index');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(\Auth::user()->isAbleTo('warehouse create'))
        {
            if(module_is_active('CustomField')){
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'pos')->where('sub_module','warehouse')->get();
            }else{
                $customFields = null;
            }
            return view('warehouses.create', compact('customFields'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(\Auth::user()->isAbleTo('warehouse create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'city'=>'required',
                    'address'=>'required',
                    'city_zip'=>'required',

                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $warehouse             = new warehouse();
            $warehouse->name       = $request->name;
            $warehouse->address    = $request->address;
            $warehouse->city       = $request->city;
            $warehouse->city_zip   = $request->city_zip;
            $warehouse->workspace  = getActiveWorkSpace();
            $warehouse->created_by = creatorId();
            $warehouse->save();

            if(module_is_active('CustomField'))
            {
                \Workdo\CustomField\Entities\CustomField::saveData($warehouse, $request->customField);
            }

            event(new CreateWarehouse($request,$warehouse));

            return redirect()->route('warehouses.index')->with('success', __('The warehouse has been created successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(warehouse $warehouse)
    {
        $id = WarehouseProduct::where('warehouse_id' , $warehouse->id)->first();

        if(\Auth::user()->isAbleTo('warehouse show'))
        {

            if(WarehouseProduct::where('warehouse_id' , $warehouse->id)->exists())
            {

                $warehouse = WarehouseProduct::where('warehouse_id' , $warehouse->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->with('product')->get();



                return view('warehouses.show', compact('warehouse'));
            }
            else
            {


                $warehouse = [];
                return view('warehouses.show', compact('warehouse'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Warehouse $warehouse)
    {
        if(\Auth::user()->isAbleTo('warehouse edit'))
        {
            if($warehouse->created_by == creatorId() && $warehouse->workspace == getActiveWorkSpace())
            {
                if(module_is_active('CustomField')){
                    $warehouse->customField = \Workdo\CustomField\Entities\CustomField::getData($warehouse, 'pos','warehouse');
                    $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'pos')->where('sub_module','warehouse')->get();
                }else{
                    $customFields = null;
                }
                return view('warehouses.edit', compact('warehouse','customFields'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        if(\Auth::user()->isAbleTo('warehouse edit'))
        {
            if($warehouse->created_by == creatorId()  && $warehouse->workspace == getActiveWorkSpace())
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $warehouse->name       = $request->name;
                $warehouse->address    = $request->address;
                $warehouse->city       = $request->city;
                $warehouse->city_zip   = $request->city_zip;
                $warehouse->save();

                if(module_is_active('CustomField'))
                {
                    \Workdo\CustomField\Entities\CustomField::saveData($warehouse, $request->customField);
                }
                event(new UpdateWarehouse($request,$warehouse));
                return redirect()->route('warehouses.index')->with('success', __('The warehouse details are updated successfully'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Warehouse $warehouse)
    {
        if(\Auth::user()->isAbleTo('warehouse delete'))
        {
            if($warehouse->created_by == creatorId()  && $warehouse->workspace == getActiveWorkSpace())
            {
                $purchase = Purchase::where('warehouse_id',$warehouse->id)->get();
                if(module_is_active('CustomField'))
                {
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('module','pos')->where('sub_module','warehouse')->get();
                    foreach($customFields as $customField)
                    {
                        $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $warehouse)->where('field_id',$customField->id)->first();
                        if(!empty($value))
                        {
                            $value->delete();
                        }
                    }
                }
                if(count($purchase) == 0)
                {
                    WarehouseProduct::where('warehouse_id',$warehouse->id)->delete();
                    event(new DestroyWarehouse($warehouse));
                    $warehouse->delete();
                }
                else
                {
                    return redirect()->route('warehouses.index')->with('error', __('This warehouse has purchase. Please remove the purchase from this warehouses.'));
                }


                return redirect()->route('warehouses.index')->with('success', __('The warehouse has been deleted'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function warehouseDetail($id)
    {
        $products = WarehouseProduct::where('product_id', '=', $id)->where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->get();
        return view('warehouses.detail', compact('products'));
    }

    public function fileImportExport()
    {
        if(Auth::user()->isAbleTo('warehouse import'))
        {
            return view('warehouses.import');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if(Auth::user()->isAbleTo('warehouse import'))
        {
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="name">Name</option>
                                    <option value="address">Address</option>
                                    <option value="city">City</option>
                                    <option value="zip_code">Zip Code</option>
                                    </select>
                                </th>
                                ';

                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '</tr>';

                        $temp_data[] = $row;

                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = 'Please Select CSV File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        }
        else
        {
            $output = array(
                'error' => __('Permission denied.'),
                'output' => null,
            );

            return json_encode($output);
        }

    }

    public function fileImportModal()
    {
        if(Auth::user()->isAbleTo('warehouse import'))
        {
            return view('warehouses.import_modal');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function warehouseImportdata(Request $request)
    {
        if(Auth::user()->isAbleTo('warehouse import'))
        {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);

            $user = \Auth::user();


            foreach ($file_data as $row) {
                    $warehouse = Warehouse::where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->Where('name', 'like',$row[$request->name])->get();

                    if($warehouse->isEmpty()){

                    try {
                        Warehouse::create([
                            'name' => $row[$request->name],
                            'address' => $row[$request->address],
                            'city' => $row[$request->city],
                            'zip_code' => $row[$request->zip_code],
                            'created_by' => creatorId(),
                            'workspace' => getActiveWorkSpace(),
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->address] . '</td>';
                        $html .= '<td>' . $row[$request->city] . '</td>';
                        $html .= '<td>' . $row[$request->zip_code] . '</td>';

                        $html .= '</tr>';
                    }
                }
                else
                {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->address] . '</td>';
                    $html .= '<td>' . $row[$request->city] . '</td>';
                    $html .= '<td>' . $row[$request->zip_code] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1)
            {

                return response()->json([
                            'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => __('Data Imported Successfully'),
                ]);
            }
        }
        else
        {
            return response()->json([
                'html' => false,
                'response' => __('Permission denied.'),
            ]);
        }
    }
}

