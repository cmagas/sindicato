
function listar_avisos() {
  var table = $("#tabla_aviso").DataTable({
    columnDefs: [
      {
        target: 3,
        visible: false,
      },
    ],
    ordering: false,
    bLengthChange: false,
    searching: { regex: true },
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    pageLength: 10,
    destroy: true,
    async: false,
    processing: true,
    ajax: {
      url: "../controlador/controlador_aviso_listar.php",
      type: "POST",
    },
    columns: [
      { data: "id" },
      { data: "titulo" },
      { data: "descripcion" },
      { data: "fechaPub" },
      {
        data: "situacion",
        render: function (data, type, row) {
          if (data == "1") {
            return "<span class='label label-success'>ACTIVO</span>";
          } else {
            return "<span class='label label-danger'>INACTIVO</span>";
          }
        },
      },
      {
        defaultContent:
          "<button style='font-size:13px;' type='button' class='desactivar btn btn-danger'><i class='fa fa-trash'></i></button>&nbsp;<button style='font-size:13px;' type='button' class='activar btn btn-success'><i class='fa fa-check'></i></button>",
      },
    ],

    language: idioma_espanol,
    select: true,
  });

  document.getElementById("tabla_aviso_filter").style.display = "none";
  $("input.global_filter").on("keyup click", function () {
    filterGlobal();
  });

  $("input.column_filter").on("keyup click", function () {
    filterColumn($(this).parents("tr").attr("data-column"));
  });
}


function abrirModalRestablecer() {
  $("#modal_restablecer_contra").modal({ backdrop: "static", keyboard: false });
  $("#modal_restablecer_contra").modal("show");
  $("#modal_restablecer_contra").on('shown.bs.modal',function(){
    $("#txt_email").focus();
  })
}

function restablecer_contra()
{
  var email=$("#txt_email").val();
  if(email.length==0)
  {
    return Swal.fire("Mensaje de Advertencia","Llene los campos vacios","warning");
  }
  var caracteres="abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ0123456789";
  var contrasena="";
  for(var i=0;i<6;i++)
  {
    contrasena+=caracteres.charAt(Math.floor(Math.random()*caracteres.length));
  }

  var cadObj='{"email":"'+email+'","pass":"'+contrasena+'"}';
    
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
          $("#modal_restablecer_contra").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos nuevos enviado a su correo","success") 
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el envio","error");
        }
    }
    obtenerDatosWeb('../js/funcionesJs/paginaFuncionesJs.php',funcAjax, 'POST','funcion=1&cadObj='+cadObj,true) 
}

function abrirDocumentoPDFPag(doc)
{
  var rutaDoc="vista/contenido/archivoVistas/"+doc;
  
  $('#modalPdf').modal('show');
  $('#iframePDF').attr('src',rutaDoc);
  
  

  
}
