<?php
namespace App\Http\Controllers;

use Config;
use Dinero;
use Datatables;
use App\Models\Client;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Repositories\User\UserRepositoryContract;
use App\Repositories\Client\ClientRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;
use Excel;

class ClientsController extends Controller
{

    protected $users;
    protected $clients;
    protected $settings;

    public function __construct(
        UserRepositoryContract $users,
        ClientRepositoryContract $clients,
        SettingRepositoryContract $settings
    )
    {
        $this->users = $users;
        $this->clients = $clients;
        $this->settings = $settings;
        $this->middleware('client.create', ['only' => ['create']]);
        $this->middleware('client.update', ['only' => ['edit']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('clients.index');
    }

    /**
     * Make json respnse for datatables
     * @return mixed
     */
    public function anyData()
    {
        $clients = Client::select(['id', 'name', 'company_name', 'email', 'primary_number']);
        return Datatables::of($clients)
            ->addColumn('namelink', function ($clients) {
                return '<a href="clients/' . $clients->id . '" ">' . $clients->name . '</a>';
            })
            ->add_column('edit', '
                <a href="{{ route(\'clients.edit\', $id) }}" class="btn btn-success" >Edit</a>')
            ->add_column('delete', '
                <form action="{{ route(\'clients.destroy\', $id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="Delete" class="btn btn-danger" onClick="return confirm(\'Are you sure?\')"">

            {{csrf_field()}}
            </form>')
            ->make(true);
    }

    public function excel()
    {
        $clients = Client::all();
        $arr =array();
        $header=array('ID', 'Name', 'Email', 'Primary Number', 'Secondary Number', 'Address', 'Zip Code', 'City', 'Company Name', 'VAT', 'Company Type', 'User Assigned', 'Industry', 'Created At', 'Updated At');
        array_push($arr, $header);
        foreach($clients as $client) {
            $industry = '';
            if($client->industry != null || $client->industry == '')
            {
                $industry = ' ';
            }
            else
            {
                $industry = $client->industry->name;
            }
            $data=array(
                $client->id,
                $client->name,
                $client->email,
                $client->primary_number,
                $client->secondary_number,
                $client->address,
                $client->zipcode,
                $client->city,
                $client->company_name,
                $client->vat,
                $client->company_type,
                $client->user->name,
                $industry,
                $client->created_at,
                $client->updated_at,
            );
            array_push($arr, $data);
        }
        // dd($arr);
        Excel::create('clients', function($excel) use ($arr) {
            $excel->setTitle('Clients');
            $excel->setCreator('BIT')->setCompany('UEH BIT');
            $excel->setDescription('Clients list');
    
            $excel->sheet('sheet1', function($sheet) use ($arr) {
                $sheet->fromArray($arr, null, 'A1', false, false);
                $sheet->cells('A1:O1', function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#F2C40F');
                });
            });
    
        })->download('xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
        return view('clients.create')
            ->withUsers($this->users->getAllUsersWithDepartments())
            ->withIndustries($this->clients->listAllIndustries());
    }

    /**
     * @param StoreClientRequest $request
     * @return mixed
     */
    public function store(StoreClientRequest $request)
    {
        $this->clients->create($request->all());
        return redirect()->route('clients.index');
    }

    /**
     * @param Request $vatRequest
     * @return mixed
     */
    public function cvrapiStart(Request $vatRequest)
    {
        return redirect()->back()
            ->with('data', $this->clients->vat($vatRequest));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return mixed
     */
    public function show($id)
    {
        return view('clients.show')
            ->withClient($this->clients->find($id))
            ->withCompanyname($this->settings->getCompanyName())
            ->withInvoices($this->clients->getInvoices($id))
            ->withUsers($this->users->getAllUsersWithDepartments());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return mixed
     */
    public function edit($id)
    {
        return view('clients.edit')
            ->withClient($this->clients->find($id))
            ->withUsers($this->users->getAllUsersWithDepartments())
            ->withIndustries($this->clients->listAllIndustries());
    }

    /**
     * @param $id
     * @param UpdateClientRequest $request
     * @return mixed
     */
    public function update($id, UpdateClientRequest $request)
    {
        $this->clients->update($id, $request);
        Session()->flash('flash_message', 'Client successfully updated');
        return redirect()->route('clients.index');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $this->clients->destroy($id);

        return redirect()->route('clients.index');
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function updateAssign($id, Request $request)
    {
        $this->clients->updateAssign($id, $request);
        Session()->flash('flash_message', 'New user is assigned');
        return redirect()->back();
    }

}
