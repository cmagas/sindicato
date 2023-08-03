function validarInAsistenciaAlumno(criterio,maxValor,porcentaje,registro,valor)
{
	
	var p=(parseFloat(valor)/parseFloat(maxValor))*100;
    var pos=existeValorMatriz(valorTotalFinal,criterio);
    if(p>=parseInt(porcentajeInasistencia))
    {
        if(pos==-1)
        	valorTotalFinal.push([criterio,'-3']);
        else
        	valorTotalFinal[pos][1]='-3';
		
    }
    else
    {
    	if(pos!=-1)
        	valorTotalFinal.splice(pos,1);
    }
   
    	
}