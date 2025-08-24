<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Workdo\LandingPage\Entities\LandingPageSetting;
use Workdo\LandingPage\Entities\Pixel;
use Illuminate\Routing\Controller;

class PixelController extends Controller
{
    public function index()
    {
	    if (!Schema::hasTable('landingpage_pixels')) {
		// Display a listing of the resource.
		$pixels = Pixel::all();
		return view('landingpage::landingpage.seo.index', compact('pixels'));
        }
    }

    public function create()
    {
        $pixals_platforms = LandingPageSetting::pixel_plateforms();

        return view('landingpage::landingpage.seo.create', compact('pixals_platforms'));
    }

    public function store(Request $request)
    {
        // Store a newly created resource in storage.
        $request->validate([
            'platform' => 'required',
            'pixel_id' => 'required',
        ]);

        Pixel::create($request->all());

        return redirect()->back()->with('success', 'The pixel has been created successfully');
    }

    public function show(Pixel $pixel)
    {
        // Display the specified resource.
        return redirect()->back()->with('error', 'Not Found');
    }

    public function edit($id)
    {
        $pixel = Pixel::find($id);
        $pixals_platforms =  LandingPageSetting::pixel_plateforms();

        return view('landingpage::landingpage.seo.edit', compact('pixel','pixals_platforms'));
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'platform' => 'required',
                'pixel_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $pixel = Pixel::find($id);
        $pixel->platform = $request->platform;
        $pixel->pixel_id = $request->pixel_id;
        $pixel->save();

        return redirect()->back()->with('success', __('The pixel details are updated successfully'));
    }

    public function destroy($id)
    {
        $pixel = Pixel::find($id);
        $pixel->delete();

        return redirect()->back()->with('success', 'The pixel has been deleted');
    }
}
