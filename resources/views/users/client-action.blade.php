
<div class="row"> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;  margin-top: 0.1px !important;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id }})" data-original-title="Edit" class="edit btn btn-primary edit"> 
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    </div> 
    <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6" style="padding:0; margin-left: -10px;"> 
        <a class="cambia{{$id}}" href="javascript:void(0)"  onClick="micheckbox({{ $id }})" >
            @if ($status == '1')
                <i class="fa-solid fa-toggle-on text-success fs-4"></i>
            @else
                <i class="fa-solid fa-toggle-off text-danger fs-4"></i>
            @endif
        </a>
    </div>
</div>    
            