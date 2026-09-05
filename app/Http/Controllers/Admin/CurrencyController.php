<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Traits\SyncClientToErp;
use App\Http\Requests\Admin\Country\CreateCountryRequest;
use App\Models\CurrencyConversion;
use App\Http\Controllers\Controller;
use Flasher\Toastr\Laravel\Facade\Toastr;
use Flasher\Toastr\Prime\ToastrInterface;
use App\Http\Requests\Admin\Country\UpdateCountryRequest;

class CurrencyController extends Controller
{
    use SyncClientToErp;


    public function index()
    {
        $currencies = CurrencyConversion::get();
        return view('Admin.currency.index', compact('currencies'));

    }


    public function create()
    {
        return view('Admin.country.create');
    }

    public function store(Request $request)
    {
        Country::create([
            'country' => $request->country,
            'country_ar' => $request->country_ar,
            'message' => $request->message,
        ]);
        toastr()->success('success', 'sucessfully added');
        return redirect()->route('admin.country.index');
    }

    public function edit($id)
    {
        $currency = CurrencyConversion::find($id);
        return view('Admin.currency.edit', compact('currency'));
    }

    public function update(Request $request)
    {


        $currency = CurrencyConversion::find($request->id);

        $currency->update([
            'conversion_rate' => $request->conversion_rate,
        ]);
        $this->updateCurrencyConversion($currency, $request->id);

        toastr()->success('success', 'تم التعديل بنجاح');
        return redirect()->route('admin.currency.index');


    }

    public function destroy($id)
    {

        $country = Country::find($id);
        if ($country) {
            $country->delete();
            Toastr::addSuccess('Deleted successfully');
            return redirect()->route('admin.country.index');
        }
        toastr()->success('not found');
        return redirect()->route('admin.country.index');

    }

}