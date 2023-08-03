var table;
function listar_imagenes() {
  table = $("#tabla_galeria").DataTable({
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
      url: "contenido/funciones/controlador_galeria_listar.php",
      type: "POST",
    },
    columns: [
      { data: "id" },
      { data: "titulo" },
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
        data: "situacion",
        render: function (data, type, row) {
          if (data == "1") {
            return "<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger'><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success' disabled><i class='fa fa-check'></i></button>";
          } else {
            return "<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger' disabled><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success'><i class='fa fa-check'></i></button>";
          }
        },
      }
 
    ],

    language: idioma_espanol,
    select: true,
  });

  document.getElementById("tabla_galeria_filter").style.display = "none";
  $("input.global_filter").on("keyup click", function () {
    filterGlobal();
  });

  $("input.column_filter").on("keyup click", function () {
    filterColumn($(this).parents("tr").attr("data-column"));
  });
}

/*FUNCION DESACTIVAR UN AVISO*/
$("#tabla_galeria").on("click", ".desactivar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
  }

  Swal.fire({
    title: "¿Esta seguro de desactivar la Imagen?",
    text: "Una vez hecho esto el usuario no podrá visualizar la imagen",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
  }).then((result) => {
    if (result.value) {
      modificarEstatusGaleria(data.id, "0");
    }
  });
});

$("#tabla_galeria").on("click", ".activar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
    }
  
    Swal.fire({
      title: "¿Esta seguro de Activar La imagen?",
      text: "Una vez hecho esto el usuario podrá visualizar la imagen",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si",
    }).then((result) => {
      if (result.value) {
        modificarEstatusGaleria(data.id, "1");
      }
    });
});

function filterGlobal() {
  $("#tabla_galeria").DataTable().search($("#global_filter").val()).draw();
}

function modificarEstatusGaleria(id, estado) {
  var mensaje = "";
  var idGaleria = id;

  if (estado == "0") {
    mensaje = "Inactivo";
  } else {
    mensaje = "Activo";
  }

  var cadObj = '{"idGaleria":"' + idGaleria + '","estado":"' + estado + '"}';

  function funcAjax() {
    var resp = peticion_http.responseText;
    arrResp = resp.split("|");
    if (arrResp[0] == "1") {
        listar_imagenes();
      Swal.fire(
        "Mensaje De Confirmacion",
        "La imagen se " + mensaje + " con exito",
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
  obtenerDatosWeb(
    "contenido/funciones/paginaFuncionesGaleria.php",funcAjax,"POST","funcion=2&cadObj=" + cadObj,true);
}

function abrirModalNuevaImagen() {
  $("#modal_registro_galeria").modal({ backdrop: "static", keyboard: false });
  $("#modal_registro_galeria").modal("show");
  
}

function registrarImagenGaleria()
{
  var frm=document.getElementById('form_subir');
  var data = new FormData(frm);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function(){
    if(this.readyState==4){
        var msg = xhttp.responseText;
            switch(msg){
                case '1':
                    $('#modal_registro_galeria').modal('hide');
                    limpiarFormulario();
                    Swal.fire("Los datos se guardaron correctamente","success") 
                    $('#modal_registro_galeria').trigger('reset');
                    listar_imagenes();
                break;
                case '2':
                    $('#modal_registro_galeria').modal('hide');
                    Swal.fire("Los datos no fueron guardados","error")
                break;
                case '3':
                    Swal.fire("Todos los campos son Obligatorios","error")
                break;
                case '4':
                    Swal.fire("Error en las fechas","error")
                break;
            }
    }
  };
  xhttp.open("POST","contenido/galeria/funciones/funcionesGaleriaGuardar.php", true);
  xhttp.send(data);
}


function limpiarFormulario()
{
  document.getElementById('form_subir').reset();
}


