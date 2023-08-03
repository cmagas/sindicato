var table;
function listar_usuario() {
  table = $("#tabla_usuario").DataTable({
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
      url: "usuario/funciones/controlador_usuario_listar.php",
      type: "POST",
    },
    columns: [
      { data: "id" },
      { data: "nombre" },

      { data: "apPaterno" },
      { data: "apMaterno" },
      {
        data: "sexo",
        render: function (data, type, row) {
          if (data == "1") {
            return "FEMENINO";
          } else {
            return "MASCULINO";
          }
        },
      },
      {
        data: "email",
      },
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
        },
      }
      
    ],

    language: idioma_espanol,
    select: true,
  });

  document.getElementById("tabla_usuario_filter").style.display = "none";
  $("input.global_filter").on("keyup click", function () {
    filterGlobal();
  });
  $("input.column_filter").on("keyup click", function () {
    filterColumn($(this).parents("tr").attr("data-column"));
  });
}

/*FUNCION PRA CAMBIAR EL ESTADO DE USUARIO DESACTIVAR*/
$("#tabla_usuario").on("click", ".desactivar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
    }

  Swal.fire({
    title: "Esta seguro de desactivar al usuario?",
    text: "Una vez hecho esto el usuario no tendra acceso al sistema",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
  }).then((result) => {
    if (result.value) {
      modificarEstatusUsuario(data.id, "0");
    }
  });
});

$("#tabla_usuario").on("click", ".activar", function () {
  var data = table.row($(this).parents("tr")).data();

  if (table.row(this).child.isShown()) {
    var data = table.row(this).data();
  }

  Swal.fire({
    title: "Esta seguro de Activar al usuario?",
    text: "Una vez hecho esto el usuario tendra acceso al sistema",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si",
  }).then((result) => {
    if (result.value) {
      modificarEstatusUsuario(data.id, "1");
    }
  });
});

$("#tabla_usuario").on("click", ".editar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
    }

    $("#modal_editar_usuario").modal({ backdrop: "static", keyboard: false });
    $("#modal_editar_usuario").modal("show");
    
    $("#txtIdUsuario").val(data.id);
    $("#txt_nombre_editar").val(data.nombre);
    $("#txt_apPaterno_editar").val(data.apPaterno);
    $("#txt_apMaterno_editar").val(data.apMaterno);
    $("#txt_genero_editar").val(data.sexo).trigger("change");
    $("#txt_email_editar").val(data.email);
    $("#txt_usuario_editar").val(data.usuario);
    $("#txt_cont1_editar").val(data.pass);
    $("#txt_cont2_editar").val(data.pass);
});


function modificarEstatusUsuario(id, estado) {
  var mensaje = "";
  var idUsuario = id;

  if (estado == "0") {
    mensaje = "Inactivo";
  } else {
    mensaje = "Activo";
  }

  var cadObj = '{"idUsuario":"' + idUsuario + '","estado":"' + estado + '"}';

  function funcAjax() {
    var resp = peticion_http.responseText;
    arrResp = resp.split("|");
    if (arrResp[0] == "1") {
      listar_usuario();
      Swal.fire(
        "Mensaje De Confirmacion",
        "El usuario se " + mensaje + " con exito",
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
    "../vista/usuario/funciones/paginaFunciones.php",
    funcAjax,
    "POST",
    "funcion=2&cadObj=" + cadObj,
    true
  );
}

function filterGlobal() {
    $("#tabla_usuario").DataTable().search($("#global_filter").val()).draw();
}

function abrirModalRegistro() {
    $("#modal_registro_usuario").modal({ backdrop: "static", keyboard: false });
    $("#modal_registro_usuario").modal("show");
} 

function registrar_usuario()
{
    var nombre=$("#txt_nombre").val();
    var apPaterno=$("#txt_apPaterno").val();
    var apMaterno=$("#txt_apMaterno").val();
    var genero=$("#txt_genero").val();
    var email=$("#txt_email").val();
    var usuario=$("#txt_usuario").val();
    var pass1=$("#txt_cont1").val();
    var pass2=$("#txt_cont2").val();
    
    if(nombre.length==0 || apPaterno.length==0 || apMaterno.length==0 || genero.length==0 || email.length==0
        || usuario.length==0 || pass1.length==0 || pass2.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    if(pass1!=pass2)
    {
        return Swal.fire("Mensaje De Advertencia","Las contraseñas deben coincidir","warning");   
    }

    var cadObj='{"nombre":"'+nombre+'","apPaterno":"'+apPaterno+'","apMaterno":"'+apMaterno+'","genero":"'+genero+'","email":"'+email+'","usuario":"'+usuario+'","pass1":"'+pass1+'"}';
    
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_registro_usuario").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Nuevo Usuario Registrado","success") 
            listar_usuario();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('usuario/funciones/paginaFunciones.php',funcAjax, 'POST','funcion=1&cadObj='+cadObj,true)    
}

function modificar_usuario()
{
     var email=$("#txt_email_editar").val();
     var usuario=$("#txt_usuario_editar").val();
    var pass1=$("#txt_cont1_editar").val();
    var pass2=$("#txt_cont2_editar").val();

    var idUsuario=$("#txtIdUsuario").val();
    
    if(email.length==0 || usuario.length==0 || pass1.length==0 || pass2.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    if(pass1!=pass2)
    {
        return Swal.fire("Mensaje De Advertencia","Las contraseñas deben coincidir","warning");   
    }

    var cadObj='{"email":"'+email+'","usuario":"'+usuario+'","pass1":"'+pass1+'","idUsuario":"'+idUsuario+'"}';
    
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_editar").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Nuevo Usuario Registrado","success") 
            listar_usuario();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('../vista/usuario/funciones/paginaFunciones.php',funcAjax, 'POST','funcion=3&cadObj='+cadObj,true)    
}
