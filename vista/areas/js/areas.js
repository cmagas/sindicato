var table;
function listar_areas()
{
    table = $("#tabla_areas").DataTable({
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
          url: "areas/funciones/controlador_area_listar.php",
          type: "POST",
        },
        columns: [
          { data: "id" },
          { data: "nombre" },
    
          { data: "email" },
          {
            data: "estatus",
            render: function (data, type, row) {
              if (data == "1") {
                return "<span class='label label-success'>ACTIVO</span>";
              } else {
                return "<span class='label label-danger'>INACTIVO</span>";
              }
            },
          },

          {
            data: "estatus",
            render: function (data, type, row) {
              if (data == "1") {
                return "<button title='Editar' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-edit'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger'><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success' disabled><i class='fa fa-check'></i></button>";
              } else {
                return "<button title='Editar' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-edit'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger' disabled><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success'><i class='fa fa-check'></i></button>";
              }
            }
          }
         
        ],
    
        language: idioma_espanol,
        select: true,
      });
    
      document.getElementById("tabla_areas_filter").style.display = "none";
      $("input.global_filter").on("keyup click", function () {
        filterGlobal();
      });

      $("input.column_filter").on("keyup click", function () {
        filterColumn($(this).parents("tr").attr("data-column"));
      });
}

function abrirModalRegistro() {
    $("#modal_registro_areas").modal({ backdrop: "static", keyboard: false });
    $("#modal_registro_areas").modal("show");
}

function limpiarCamposModalAreas()
{
    $("#txt_nombreArea").val("");
    $("#txt_emailArea").val("");

    $("#txt_nombre_editar_area").val("");
    $("#txt_email_editar_area").val("");
    $("#txtIdArea").val("");

}

function registrar_areas()
{
    var nomArea=$("#txt_nombreArea").val();
    var emailArea=$("#txt_emailArea").val();

    if(nomArea.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"nombre":"'+nomArea+'","email":"'+emailArea+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_registro_areas").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Nueva Area Registrada","success") 
            listar_areas();
            limpiarCamposModalAreas();          
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('areas/funciones/paginaFuncionesAreas.php',funcAjax, 'POST','funcion=1&cadObj='+cadObj,true)  
}

/*FUNCION PRA CAMBIAR EL ESTADO DE AREA DESACTIVAR*/
$("#tabla_areas").on("click", ".desactivar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
      }
  
    Swal.fire({
      title: "¿Esta seguro de desactivar el Area?",
      text: "Una vez hecho esto el usuario no visualizará el Area",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si",
    }).then((result) => {
      if (result.value) {
        modificarEstatusArea(data.id, "0");
      }
    });
  });

$("#tabla_areas").on("click", ".activar", function () {
var data = table.row($(this).parents("tr")).data();

if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
}

Swal.fire({
    title: "¿Esta seguro de Activar el Area?",
    text: "Una vez hecho esto el usuario podrá visualizar el Area",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
}).then((result) => {
    if (result.value) {
    modificarEstatusArea(data.id, "1");
    }
});
});

$("#tabla_areas").on("click", ".editar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
    }

    $("#modal_editar_area").modal({ backdrop: "static", keyboard: false });
    $("#modal_editar_area").modal("show");
    
    $("#txtIdArea").val(data.id);
    $("#txt_nombre_editar_area").val(data.nombre);
    $("#txt_email_editar_area").val(data.email);
    
});

  function modificarEstatusArea(id,estado)
  {
    var mensaje = "";
    var idArea = id;

    if (estado == "0") {
        mensaje = "Inactivo";
    } else {
        mensaje = "Activo";
    }

    var cadObj = '{"idArea":"' + idArea + '","estado":"' + estado + '"}';

    function funcAjax() {
        var resp = peticion_http.responseText;
        arrResp = resp.split("|");
        if (arrResp[0] == "1") {
            listar_areas();
        Swal.fire(
            "Mensaje De Confirmacion",
            "El Area se " + mensaje + " con exito",
            "success"
        );
        } else {
        Swal.fire(
            "Mensaje De Error",
            "Lo sentimos, no se pudo modificar el registro",
            "error"
        );
        }
    }
    obtenerDatosWeb("areas/funciones/paginaFuncionesAreas.php",funcAjax,"POST","funcion=2&cadObj=" + cadObj,true);
  }

function modificar_areas()
{
    var nomArea=$("#txt_nombre_editar_area").val();
    var emailArea=$("#txt_email_editar_area").val();
    var idArea=$("#txtIdArea").val();

    if(nomArea.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"nombre":"'+nomArea+'","email":"'+emailArea+'","idArea":"'+idArea+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_editar_area").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Area Modificada","success") 
            listar_areas();
            limpiarCamposModalAreas();          
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('areas/funciones/paginaFuncionesAreas.php',funcAjax, 'POST','funcion=3&cadObj='+cadObj,true) 
}
    