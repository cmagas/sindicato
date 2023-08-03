var table;
function listar_avisos() {
  table = $("#tabla_aviso").DataTable({
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
      url: "contenido/funciones/controlador_aviso_listar.php",
      type: "POST",
    },
    columns: [
      { data: "id" },
      { data: "titulo" },
      { data:"nombreArea"},
      { data: "fechaPub" },
      { data: "fechaFin" },
      {
        data: "docExiste",
        render: function (data, type, row) {
          if (data == "0") {
            return "No";
          } else {
            return "Si";
          }
        },
      },
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
            return "<button title='Visualizar Doc.' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-file-pdf-o'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger'><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success' disabled><i class='fa fa-check'></i></button>";
          } else {
            return "<button title='Visualizar Doc.' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-file-pdf-o'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger' disabled><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success'><i class='fa fa-check'></i></button>";
          }
        },
      }      
      
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

function abrirModalNuevoAviso() {
  $("#modal_registro_aviso").modal({ backdrop: "static", keyboard: false });
  $("#modal_registro_aviso").modal("show");
  $("#modal_registro_aviso").on("shown.bs.modal", function () {
    //$("#txt_email").focus();
  });
}

/*FUNCION DESACTIVAR UN AVISO*/
$("#tabla_aviso").on("click", ".desactivar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
  }

  Swal.fire({
    title: "¿Esta seguro de desactivar el aviso?",
    text: "Una vez hecho esto el usuario no visualizará el aviso en la página principal",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
  }).then((result) => {
    if (result.value) {
      modificarEstatusAviso(data.id, "0");
    }
  });
});

/*FUNCION ACTIVAR UN AVISO*/
$("#tabla_aviso").on("click", ".activar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
  }

  Swal.fire({
    title: "Esta seguro de Activar el aviso?",
    text: "Una vez hecho esto el usuario podrá visualizar el Aviso",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
  }).then((result) => {
    if (result.value) {
      modificarEstatusAviso(data.id, "1");
    }
  });
});

function modificarEstatusAviso(id, estado) {
  var mensaje = "";
  var idAviso = id;

  if (estado == "0") {
    mensaje = "Inactivo";
  } else {
    mensaje = "Activo";
  }

  var cadObj = '{"idAviso":"' + idAviso + '","estado":"' + estado + '"}';

  function funcAjax() {
    var resp = peticion_http.responseText;
    arrResp = resp.split("|");
    if (arrResp[0] == "1") {
      listar_avisos();
      Swal.fire(
        "Mensaje De Confirmacion",
        "El Aviso se " + mensaje + " con exito",
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
    "contenido/funciones/paginaFuncionesAviso.php",funcAjax,"POST","funcion=1&cadObj=" + cadObj,true);
}

/*VER ARCHIVO MODAL */
$("#tabla_aviso").on("click", ".editar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
  }

  abrirDocumentoPDF(data.url);
  
});

function abrirDocumentoPDF(doc)
{
  var rutaDoc="contenido/archivoVistas/"+doc;
  $('#modalPdf').modal('show');
  $('#iframePDF').attr('src',rutaDoc);
  listar_avisos();
}



