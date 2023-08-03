var table;
function listar_noticias() {
    table = $("#tabla_noticias").DataTable({
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
        url: "contenido/funciones/controlador_noticias_listar.php",
        type: "POST",
      },
      columns: [
        { data: "id" },
        { data: "titulo" },
        { data:"descripcion"},
        { data: "fechaPub"},        
        { data: "fechaFin" },
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
              return "<button title='Modificar' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-edit'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger'><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success' disabled><i class='fa fa-check'></i></button>";
            } else {
              return "<button title='Modificar' style='font-size:13px;' type='button' class='editar btn btn-primary'><i class='fa fa-edit'></i></button>&nbsp;<button title='Inactivar' style='font-size:13px;' type='button' class='desactivar btn btn-danger' disabled><i class='fa fa-trash'></i></button>&nbsp;<button title='Activar' style='font-size:13px;' type='button' class='activar btn btn-success'><i class='fa fa-check'></i></button>";
            }
          },
        }

      ],
  
      language: idioma_espanol,
      select: true,
    });
  
    document.getElementById("tabla_noticias_filter").style.display = "none";
    $("input.global_filter").on("keyup click", function () {
      filterGlobal();
    });
  
    $("input.column_filter").on("keyup click", function () {
      filterColumn($(this).parents("tr").attr("data-column"));
    });
}

function abrirModalNuevaNoticia() {
    $("#modal_registro_noticia").modal({ backdrop: "static", keyboard: false });
    $("#modal_registro_noticia").modal("show");
    $("#modal_registro_noticia").on("shown.bs.modal", function () {
      //$("#txt_email").focus();
    });
}

function registrarNuevaNoticia()
{
    var titulo=$("#txt_titulo").val();
    var descricion=$("#desc_corta").val();
    var fechaPub=$("#fecha_aplica").val();
    var fechaFin=$("#fecha_finaliza").val();
    var idArea=$("#cmb_areas").val();

    if(titulo.length==0 || descricion.length==0 || fechaPub.length==0 || fechaFin.length==0 || idArea.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"titulo":"'+titulo+'","descricion":"'+descricion+'","fechaPub":"'+fechaPub+'","fechaFin":"'+fechaFin+'","idArea":"'+idArea+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_registro_noticia").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Nueva Noticia Registrada","success") 
            listar_noticias();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('contenido/funciones/paginaFuncionesNoticias.php',funcAjax, 'POST','funcion=1&cadObj='+cadObj,true) 

}

/*FUNCION PRA CAMBIAR EL ESTADO DE USUARIO DESACTIVAR*/
$("#tabla_noticias").on("click", ".desactivar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
      }
  
        Swal.fire({
        title: "¿Esta seguro de desactivar la Noticia?",
        text: "Una vez hecho esto el usuario no podrá visualizarlo",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si",
        }).then((result) => {
        if (result.value) {
            modificarEstatusNoticias(data.id, "0");
        }
        });
 });

 $("#tabla_noticias").on("click", ".activar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
    }
  
    Swal.fire({
      title: "¿Esta seguro de Activar la Noticia?",
      text: "Una vez hecho esto el usuario podrá visualizarla",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si",
    }).then((result) => {
      if (result.value) {
        modificarEstatusNoticias(data.id, "1");
      }
    });
  });

  $("#tabla_noticias").on("click", ".editar", function () {
    var data = table.row($(this).parents("tr")).data();
  
    if (table.row(this).child.isShown()) {
      var data = table.row(this).data();
    }

    $("#modal_modificar_noticia").modal({ backdrop: "static", keyboard: false });
    $("#modal_modificar_noticia").modal("show");

    $("#txt_modificar_titulo").val(data.titulo);
    $("#desc_corta_modificar").val(data.descripcion);
    var fechaAplica=formatearFechaNormal(data.fechaPub);
    var fechaFinal=formatearFechaNormal(data.fechaFin);
    $("#fecha_aplica_modificar").val(fechaAplica);
    $("#fecha_finaliza_modificar").val(fechaFinal);
    $("#cmb_areas_modificar").val(data.idArea);
    $("#txtIdNoticias").val(data.id);
    
  });

  function modificarEstatusNoticias(id,estado)
  {
    var mensaje = "";
  var idNoticia = id;

  if (estado == "0") {
    mensaje = "Inactivo";
  } else {
    mensaje = "Activo";
  }

  var cadObj = '{"idNoticia":"' + idNoticia + '","estado":"' + estado + '"}';

  function funcAjax() {
    var resp = peticion_http.responseText;
    arrResp = resp.split("|");
    if (arrResp[0] == "1") {
        listar_noticias();
      Swal.fire(
        "Mensaje De Confirmacion",
        "La Noticia se " + mensaje + " con exito",
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
  obtenerDatosWeb("contenido/funciones/paginaFuncionesNoticias.php",funcAjax,"POST","funcion=2&cadObj=" + cadObj,true);
  }

  function registrarModificacionNoticia()
  {
    var titulo=$("#txt_modificar_titulo").val();
    var descripcion=$("#desc_corta_modificar").val();
    var fechaPub=$("#fecha_aplica_modificar").val();
    var fechaFin=$("#fecha_finaliza_modificar").val();
    var idArea=$("#cmb_areas_modificar").val();
    var idNoticia=$("#txtIdNoticias").val();

    if(titulo.length==0 || descripcion.length==0 || fechaPub.length==0 || fechaFin.length==0 || idArea.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"titulo":"'+titulo+'","descripcion":"'+descripcion+'","fechaPub":"'+fechaPub+'","fechaFin":"'+fechaFin+'","idArea":"'+idArea+'","idNoticia":"'+idNoticia+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_modificar_noticia").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente registrados","success") 
            listar_noticias();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('contenido/funciones/paginaFuncionesNoticias.php',funcAjax, 'POST','funcion=3&cadObj='+cadObj,true) 
  }

  function formatearFechaNormal(val)
{
    if(val!="")
    {
    	var arrValor=val.split('/');
        if(val.indexOf(':')==-1)
        {
        	return arrValor[2]+'-'+arrValor[1]+'-'+arrValor[0];	
        }
        else
        {
        	var anio=arrValor[2];
            var datos=anio.split(' ');
            anio=datos[0];
            var comp=' ';
            if(datos[1]!='00:00:00')
            {
            	comp=' '+datos[1];
            }
        	return anio+'-'+arrValor[1]+'-'+arrValor[0]+comp;
        }
	
    }
    return '';
}