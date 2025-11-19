<div class="d-flex justify-content-center align-items-center" style="gap: 1px;">
    <a href="{{ route('pdfTicket', ['id' => $id]) }}" data-toggle="tooltip" target="_blank" data-original-title="Ver" class="btn btn-info btn-sm mx-1" style="padding: 4px 3px;">
        <i class="fa-regular fa-eye" style="color: White !important;"></i>
    </a>
    @if ($status == 'TERMINADO' || $status == 'ENTREGADO')
        <a href="javascript:void(0)" data-toggle="tooltip" onClick="addTechnicianModal({{ $id }})" data-original-title="Técnico" class="btn btn-success btn-sm mx-1 disabled" style="padding: 4px 3px;">
            <i class="fa-solid fa-user-plus"></i>
        </a>
    @else
        <a href="javascript:void(0)" data-toggle="tooltip" onClick="addTechnicianModal({{ $id }})" data-original-title="Técnico" class="btn btn-success btn-sm mx-1" style="padding: 4px 3px;">
            <i class="fa-solid fa-user-plus"></i>
        </a>
    @endif
    @if ($id_technician == null)
        <a href="javascript:void(0)" data-toggle="tooltip" onClick="addSolution({{ $id }})" data-original-title="Solución" class="btn btn-primary btn-sm mx-1 disabled" style="padding: 4px 3px;">
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    @else
        @if ($status == 'TERMINADO' || $status == 'ENTREGADO')
            <a href="javascript:void(0)" data-toggle="tooltip" onClick="mostrarService({{ $id }})" data-original-title="Ver Solución" class="btn btn-primary btn-sm mx-1" style="padding: 4px 3px;">
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        @else
            <a href="javascript:void(0)" data-toggle="tooltip" onClick="addSolution({{ $id }})" data-original-title="Solución" class="btn btn-primary btn-sm mx-1" style="padding: 4px 3px;">
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        @endif
    @endif
    <a href="javascript:void(0)" data-toggle="tooltip" onClick="editFunc({{ $id_client }})" data-original-title="Cliente" class="btn btn-info btn-sm mx-1" style="padding: 4px 3px;">
        <i class="fa-solid fa-user-tag" style="color: White !important;"></i>
    </a>
    <a class="btn btn-success btn-sm mx-1 whatsapp-button" style="padding: 4px 3px; background-color: #25D366 !important;" href="{{ route('openWhatsAppChatService', ['phone' => $phone, 'status' => $status]) }}" target="_blank">
        <i class="fa-brands fa-whatsapp" style="color: White !important;"></i>
    </a>
</div>
            