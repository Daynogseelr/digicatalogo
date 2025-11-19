
<div class="row text-center"> 
    @if (auth()->user()->type == "EMPRESA" || auth()->user()->type == "ADMINISTRATIVO" || auth()->user()->type == "ADMINISTRADOR" || auth()->user()->type == "SUPERVISOR")
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" >  
            <a style="margin: -4px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id }})" data-original-title="Edit" class="edit btn btn-primary"> 
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div> 
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style=" text-align:center; ">  
            <a style="margin: -4px !important; padding: 5px; color:white;"  href="javascript:void(0)" data-toggle="tooltip" onClick="discount({{ $id }})" data-original-title="Edit" class="edit btn btn-info"> 
                <i class="fa fa-arrow-down"></i>
            </a>
        </div> 
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style=" margin-left: -10px;"> 
            <a class="cambia{{$id}}" href="javascript:void(0)"  onClick="micheckbox({{ $id }})" >
                @if ($status == '1')
                    <i class="fa-solid fa-toggle-on text-success fs-4"></i>
                @else
                    <i class="fa-solid fa-toggle-off text-danger fs-4"></i>
                @endif
            </a>
        </div>
    @endif
</div>    
            