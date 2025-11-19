
<div class="row text-center"> 
    @if (auth()->user()->type == "EMPRESA")
        @if ($status == "FINALIZADO" || $status == "INCONCLUSO")
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarOrder({{ $id }})" data-original-title="Edit" class="edit btn btn-primary "> 
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
            </div> 
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id_client }})" data-original-title="Edit" class="edit btn btn-info "> 
                    <i class="fa-solid fa-user-tag" style="color: White  !important;"></i>
                </a>
            </div> 
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px; background-color: #25D366; !important;" class="whatsapp-button btn edit" href="{{ route('openWhatsAppChat', ['phone' => $phone, 'status' => $status]) }}" target="_blank">
                    <i class="fa-brands fa-whatsapp"  style="color: White  !important;"></i>
                </a>
            </div> 
        @else
            <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="addSellerModal({{ $id }})" data-original-title="Edit" class="edit btn btn-success "> 
                    <i class="fa-solid fa-user-plus"></i>
                </a>
            </div> 
            <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarOrder({{ $id }})" data-original-title="Edit" class="edit btn btn-primary "> 
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
            </div> 
            <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id_client }})" data-original-title="Edit" class="edit btn btn-info "> 
                    <i class="fa-solid fa-user-tag" style="color: White  !important;"></i>
                </a>
            </div> 
            <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3" style="padding:0;">  
                <a style="margin: -6px !important; padding: 5px; background-color: #25D366; !important;" class="whatsapp-button btn edit" href="{{ route('openWhatsAppChat', ['phone' => $phone, 'status' => $status]) }}" target="_blank">
                    <i class="fa-brands fa-whatsapp"  style="color: White  !important;"></i>
                </a>
            </div> 
        @endif 
    @else 
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarOrder({{ $id }})" data-original-title="Edit" class="edit btn btn-primary "> 
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div> 
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px;"  href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id_client }})" data-original-title="Edit" class="edit btn btn-info "> 
                <i class="fa-solid fa-user-tag" style="color: White  !important;"></i>
            </a>
        </div> 
        <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
            <a style="margin: -6px !important; padding: 5px; background-color: #25D366; !important;" class="whatsapp-button btn edit" href="{{ route('openWhatsAppChat', ['phone' => $phone, 'status' => $status,]) }}" target="_blank">
                <i class="fa-brands fa-whatsapp"  style="color: White  !important;"></i>
            </a>
        </div> 
    @endif  
</div>    
            