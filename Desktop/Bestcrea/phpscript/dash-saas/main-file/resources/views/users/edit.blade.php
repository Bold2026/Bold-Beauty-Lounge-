{{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            @if(Auth::user()->type == 'super admin')
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    </div>
                </div>
            @else
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                    </div>
                </div>
            @endif
            <x-mobile value="{{ !empty($user->mobile_no) ? $user->mobile_no : null }}"></x-mobile>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
{{Form::close()}}
