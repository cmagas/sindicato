var table;
function listar_eventos() {
    table = $("#tabla_eventos").DataTable({
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
        url: "contenido/funciones/controlador_evento_listar.php",
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
  
    document.getElementById("tabla_eventos_filter").style.display = "none";
    $("input.global_filter").on("keyup click", function () {
      filterGlobal();
    });
  
    $("input.column_filter").on("keyup click", function () {
      filterColumn($(this).parents("tr").attr("data-column"));
    });
}

function abrirModalNuevoEvento() {
    $("#modal_registro_evento").modal({ backdrop: "static", keyboard: false });
    $("#modal_registro_evento").modal("show");
    $("#modal_registro_evento").on("shown.bs.modal", function () {
      //$("#txt_email").focus();
    });
}

  function registrarEventos()
  {
    var titulo=$("#txt_titulo").val();
    var descripcion=$("#desc_corta").val();
    var fechaEvento=$("#fecha_evento").val();
    var horaEVento=$("#hora_evento").val();
    var lugarEvento=$("#lugar_evento").val();
    var fechaAplicacion=$("#fecha_aplica").val();
    var fechaFin=$("#fecha_finaliza").val();

    if(titulo.length==0 || descripcion.length==0 || fechaEvento.length==0 || lugarEvento.length==0 || fechaAplicacion.length==0 || fechaFin.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"titulo":"'+titulo+'","descripcion":"'+descripcion+'","fechaEvento":"'+fechaEvento+'","horaEVento":"'+horaEVento+'","lugarEvento":"'+lugarEvento+'","fechaAplicacion":"'+fechaAplicacion+'","fechaFin":"'+fechaFin+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_registro_evento").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Nuevo Evento Registrado","success") 
            listar_eventos();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('contenido/funciones/paginaFuncionesEventos.php',funcAjax, 'POST','funcion=1&cadObj='+cadObj,true)    

  }

  /*FUNCION DESACTIVAR UN AVISO*/
    $("#tabla_eventos").on("click", ".desactivar", function () {
        var data = table.row($(this).parents("tr")).data();
    
        if (table.row(this).child.isShown()) {
        var data = table.row(this).data();
        }
    
        Swal.fire({
        title: "¿Esta seguro de desactivar el Evento?",
        text: "Una vez hecho esto el usuario no visualizará el evento en la página principal",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si",
        }).then((result) => {
        if (result.value) {
            modificarEstatusEvento(data.id, "0");
        }
        });
    });

/*FUNCION ACTIVAR UN AVISO*/
    $("#tabla_eventos").on("click", ".activar", function () {
        var data = table.row($(this).parents("tr")).data();
    
        if (table.row(this).child.isShown()) {
        var data = table.row(this).data();
        }
    
        Swal.fire({
        title: "Esta seguro de Activar el eventos?",
        text: "Una vez hecho esto el usuario podrá visualizar el Evento",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si",
        }).then((result) => {
        if (result.value) {
            modificarEstatusEvento(data.id, "1");
        }
        });
    });

    /*MODIFICAR EVENTOS*/
    $("#tabla_eventos").on("click", ".editar", function () {
        var data = table.row($(this).parents("tr")).data();
      
        if (table.row(this).child.isShown()) {
          var data = table.row(this).data();
        }
    
        $("#modal_editar_evento").modal({ backdrop: "static", keyboard: false });
        $("#modal_editar_evento").modal("show");

        $("#txtIdEvento").val(data.id);
        $("#txt_modificar_titulo").val(data.titulo);
        $("#desc_corta_modificar").val(data.descripcion);
        var fechaEvento=formatearFechaNormal(data.fechaEvento);
        $("#fecha_evento_modificar").val(fechaEvento);
        $("#hora_evento_modificar").val(data.hora);
        $("#lugar_evento_modificar").val(data.lugar);
        var fechaPubli=formatearFechaNormal(data.fechaPub);
        $("#fecha_aplica_modificar").val(fechaPubli);
        var fechaFinal=formatearFechaNormal(data.fechaFin);
        $("#fecha_finaliza_modificar").val(fechaFinal);

    });

    function filterGlobal() {
        $("#tabla_eventos").DataTable().search($("#global_filter").val()).draw();
    }

function modificarEstatusEvento(id,estado)
{
    var mensaje = "";
    var idEvento = id;

    if (estado == "0") {
        mensaje = "Inactivo";
    } else {
        mensaje = "Activo";
    }

    var cadObj = '{"idEvento":"' + idEvento + '","estado":"' + estado + '"}';

    function funcAjax() {
        var resp = peticion_http.responseText;
        arrResp = resp.split("|");
        if (arrResp[0] == "1") {
            listar_eventos();
        Swal.fire(
            "Mensaje De Confirmacion",
            "El evento se " + mensaje + " con exito",
            "success"
        );
        } else {
        Swal.fire(
            "Mensaje de Error",
            "Lo sentimos, no se pudo modificar el registro",
            "error"
        );
        }
    }
    obtenerDatosWeb("contenido/funciones/paginaFuncionesEventos.php",funcAjax,"POST","funcion=2&cadObj=" + cadObj,true);
}

function modificarEventos()
{
    var idEvento= $("#txtIdEvento").val();
    var titulo=$("#txt_modificar_titulo").val();
    var descripcion=$("#desc_corta_modificar").val();
    var fechaEvento=$("#fecha_evento_modificar").val();
    var horaEvento= $("#hora_evento_modificar").val();
    var lugarEvento=$("#lugar_evento_modificar").val();
    var fechaAplicacion=$("#fecha_aplica_modificar").val();
    var fechaFin=$("#fecha_finaliza_modificar").val();

    if(titulo.length==0 || descripcion.length==0 || fechaEvento.length==0 || lugarEvento.length==0 || fechaAplicacion.length==0 || fechaFin.length==0)
    {
        return Swal.fire("Mensaje De Advertencia","Todos los campos son obligatorios","warning");
    }

    var cadObj='{"titulo":"'+titulo+'","descripcion":"'+descripcion+'","fechaEvento":"'+fechaEvento+'","horaEvento":"'+horaEvento+'","lugarEvento":"'+lugarEvento+'","fechaAplicacion":"'+fechaAplicacion+'","fechaFin":"'+fechaFin+'","idEvento":"'+idEvento+'"}';

    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            $("#modal_editar_evento").modal('hide');
            Swal.fire("Mensaje De Confirmacion","Datos correctamente, Evento Modificado","success") 
            listar_eventos();           
        }
        else
        {
            Swal.fire("Mensaje De Error","Lo sentimos, no se pudo completar el registro","error");
        }
    }
    obtenerDatosWeb('contenido/funciones/paginaFuncionesEventos.php',funcAjax, 'POST','funcion=3&cadObj='+cadObj,true)    

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