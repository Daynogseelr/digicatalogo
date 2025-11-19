
<div class="row text-center"> 
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarOrder({{ $id }})" title="Edit" class="edit btn btn-primary "> 
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    </div> 
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id_company }})" title="Edit" class="edit btn btn-info "> 
            <i class="fa-solid fa-user-tag" style="color: White  !important;"></i>
        </a>
    </div> 
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
        <a style="margin: -6px !important; padding: 5px;  background-color: #25D366; !important;" class="whatsapp-button edit btn" href="{{ route('openWhatsAppChatClient', ['phone' => $phone]) }}" target="_blank">
            <i class="fa-brands fa-whatsapp"  style="color: White  !important;"></i>
        </a>
    </div> 
</div>    
 
            