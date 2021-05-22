<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Student;
use DataTables;
use Validator;

/**
 * For this class I tried to use datatables because I need the search bar
 */

class StudentController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:student-list|student-create|student-edit|student-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:student-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:student-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:student-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $rooms = Room::all();
        return view('students.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new student.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'nim'   => 'required|min:10|max:10',
            'phone' => 'required',
            'email' => 'required|email',
            'roomId' => 'required',
            'photo' => 'mimes:jpg,bmp,png|max:1024',
        ]);

        if($validator->passes()){
            $input = $request->all();
            if ($photo = $request->file('photo')) {
                $fileName       = Request()->nim . '.' . $photo->extension();
                $photo->move(public_path('student_photo'), $fileName);
                $input['photo'] = $fileName;
            }else{
                unset($input['photo']);
            }

            Student::updateOrCreate(['id' => $request->id], $input);
            return response()->json(['success'=>'Added new student']);
        }
        
        return response()->json(['error'=>$validator->errors()->all()]);
    }

    /**
     * Display the student details.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::findOrFail($id);
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }

    /**
     * Update the specified student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified student from storage.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Student::find($id)->delete();
        return response()->json(['success' => 'Student deleted successfully.']);
    }

    /**
     * Function to display datatable
     * Create action button, linked to /students/action.blade.php
     * Use query() instead of select / all, so we can use for many queries
     */
    public function dataTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Student::latest()->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($student){
                        return view('students.action', [
                            'student'       => $student,
                            'url_show'      => route('students.show', $student->id),
                            'url_edit'      => route('students.edit', $student->id),
                            'url_destroy'   => route('students.destroy', $student->id)
                        ]);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
    }
}
