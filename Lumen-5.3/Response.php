<?php

# Basic response
$app->get('/', function() {
    return 'sth';
});

# 响应对象
use Illuminate\Http\Response;

Route::get('home', function(){
    return (new Response($content, $status))
                ->header('Content-Type', $value);
});

Route::get('home', function() {
    return response($content, $status)
            ->header('Content-Type', $value);
});

# 附加标头至响应
return response($content)
            ->header([
                'Content-Type' => $type,
                'X-Header-One' => 'Header Value',
                'X-Header-Two' => 'Header Value',
            ]);

# JSON
return response()->json(['name' => 'Abigail', 'state' => 'CA']);

return response()->json(['name' => 'Abigail', 'state' => 'CA'])
                 ->setCallback($request->input('callback'));

# file download
return response()->download($pathToFile);
return response()->download($pathToFile, $name, $header);

# redirect
$app->get('dashboard', function() {
    return redirect('home/dashboard');
});

return redirect()->route('login');

return redirect()->route('profile', ['id' => 1]);

return redirect()->route('profile', [$user]);