function registrarAviso()
{
    var frm=document.getElementById('form_subir');
    var data = new FormData(frm);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(){
        if(this.readyState==4){
            var msg = xhttp.responseText;
                
                switch(msg){
                    case '1':
                        $('#modal_registro_aviso').modal('hide');
                        Swal.fire("Los datos se guardaron correctamente","success") 
                        $('#modal_registro_aviso').trigger('reset');
                        listar_avisos();
                    break;
                    case '2':
                        $('#modal_registro_aviso').modal('hide');
                        Swal.fire("Los datos no fueron guardados","error")
                    break;
                    case '3':
                        Swal.fire("El documento debe ser PDF","error")
                    break;
                }
                    
        }
    };
    xhttp.open("POST","../js/funcionesJs/funcionesAviso.php", true);
    xhttp.send(data);
    
}
