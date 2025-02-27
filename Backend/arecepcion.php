<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    include("conexion.php");
    
    $postjson = json_decode(file_get_contents("php://input"), TRUE);


    if($postjson['aksi'] == "registrar-ar"){
        $nro_acta = $postjson['nro_acta'];
        $fecha = $postjson['fecha'];
        $hora = $postjson['hora'];
        $tipo_rescate = $postjson['tipo_rescate'];
        
        $id_municipio_E = $postjson['id_municipio_E'];
        $barrio = $postjson['barrio'];
        $calle = $postjson['calle'];
        $nro_casa = $postjson['nro_casa'];
        
        $id_municipio_S = $postjson['id_municipio_S'];
        $barrioS = $postjson['barrioS'];
        $calleS = $postjson['calleS'];
        $empresa = $postjson['empresa'];
        $area = $postjson['area'];
        
        $especie_proc = $postjson['especie_proc'];
        $id_usuario = $postjson['id_usuario'];
        
        $cedulaP = $postjson['cedulaP'];
        $nombreP = $postjson['nombreP'];
        $telefonoP = $postjson['telefonoP'];
        $firmaP = $postjson['firmaP'];
        $id_animal_silvestre;
        $observaciones;
        
        //registro de persona
        $res = $mysqli->query("INSERT INTO tblpersonas SET nombreC='$nombreP', firma='$firmaP', telefono='$telefonoP', cedula='$cedulaP'");
        
        $repPersona = $mysqli->query("SELECT id_persona FROM tblpersonas WHERE cedula='$cedulaP'");
        $id_person = mysqli_fetch_array($repPersona);
        $id_person = $id_person["id_persona"];

        $res2 = $mysqli->query("INSERT INTO acta_recepcion SET 
        num_acta_ar='$nro_acta',
        fecha_ar='$fecha',
        hora_ar='$hora',
        id_tipo_atencion=$tipo_rescate,
        id_ldfe_municipio=$id_municipio_E,
        nom_ldfe_barrio_ar='$barrio',
        nom_ldfecalle_ar='$calle',
        num_ldfe_casa_ar='$nro_casa',
        id_ldp_municipio=$id_municipio_S,
        nom_ldp_barrio_ar='$barrioS',
        nom_ldp_calle_ar='$calleS',
        nom_ldp_empresa_ar='$empresa',
        nom_ldp_area_ar='$area',
        id_usuario=$id_usuario,
        id_persona=$id_person");
        
        $repActa = $mysqli->query("SELECT id_acta_recepcion FROM acta_recepcion WHERE num_acta_ar='$nro_acta' AND fecha_ar='$fecha' AND hora_ar='$hora' AND id_persona=$id_person");
        $id_acta = mysqli_fetch_array($repActa);
        $id_acta = $id_acta["id_acta_recepcion"];
        
        
        for($i = 0; $i < count($especie_proc); $i++){
            $animal_silvestre[$i]= $especie_proc[$i]['animal_silvestre'];
            $observaciones[$i]= $especie_proc[$i]['observacion'];
        }
        
        for($i = 0; $i < count($especie_proc); $i++){
            $res3 = $mysqli->query("INSERT INTO procedente_atencion SET id_acta_recepcion=$id_acta,
                id_animal_silvestre=$animal_silvestre[$i],
                observaciones_rec='$observaciones[$i]'");
        }
        
        if($res && $res2 && $res3){
            $result = json_encode(array("success" => TRUE, "msg" => "Acta de recepción registrada con exito"));
        }else{
            $result = json_encode(array("success" => FALSE, "msg" => "Hubo un error al registrar el Acta de recepción"));
        }

        echo $result;
        
    
    }else if($postjson['aksi'] == "ver-acta"){

        $res = $mysqli->query("SELECT AR.num_acta_ar as nro_acta, AR.fecha_ar as fecha, AR.hora_ar as hora,
        A.nom_tipo_atencion, MS.nom_mun, 
        AR.nom_ldfe_barrio_ar as barrioE, AR.nom_ldfecalle_ar as calleE, AR.num_ldfe_casa_ar as nro_casaE,
        AR.nom_ldp_barrio_ar as barrioS, AR.nom_ldp_calle_ar as calleS, 
        AR.nom_ldp_empresa_ar as empresa, AR.nom_ldp_area_ar as area, U.nombre_u, U.ci_u, U.firma_u,
        P.nombreC, P.cedula, P.telefono, P.firma
        FROM acta_recepcion as AR
        INNER JOIN tatencion as A
        ON AR.id_tipo_atencion=A.id_tipo_atencion
        INNER JOIN municipios as MS
        ON AR.id_ldfe_municipio=MS.id_municipio
        INNER JOIN usuarios as U
        ON AR.id_usuario=U.id_usuario
        INNER JOIN tblpersonas as P
        ON AR.id_persona=P.id_persona
        WHERE AR.num_acta_ar='$num_acta_ar'");
        
        $res2 = $mysqli->query("SELECT MS.nom_mun FROM acta_recepcion as AR
        INNER JOIN municipios as MS
        ON AR.id_ldp_municipio=MS.id_municipio");
        
        $nombreMuS = mysqli_fetch_array($res2);
        $nombreMuS = $nombreMuS["nom_mun"];
        
        $res3 = $mysqli->query("SELECT PA.nombre_cientifico, PA.nombre_comun, E.nom_edad, S.nom_sexo, PA.observaciones_rec
            FROM acta_recepcion as AR
            INNER JOIN procedente_atencion as PA
            ON AR.id_acta_recepcion=PA.id_acta_recepcion
            INNER JOIN edad as E
            ON PA.id_edad=E.id_edad
            INNER JOIN sexo as S
            ON PA.id_sexo=S.id_sexo
            WHERE AR.num_acta_ar='$num_acta_ar'");
            
        
        
        $check =mysqli_num_rows($res);
        $check2 =mysqli_num_rows($res3);
        
        if($check > 0 && $check2 > 0){
            $data= mysqli_fetch_array($res);
            $datauser = array(
            'nro_acta' => $data["nro_acta"],
            'fecha' => $data["fecha"],
            'hora' => $data["hora"],
            'tipo_atencion' => $data["nom_tipo_atencion"],
            'municipioE' => $data["nom_mun"],
            'barrioE' => $data["barrioE"],
            'calleE' => $data["calleE"],
            'nro_casaE' => $data["nro_casaE"],
            
            'municipioS' => $nombreMuS,
            'barrioS' => $data["barrioS"],
            'calleS' => $data["calleS"],
            'empresa' => $data["empresa"],
            'area' => $data["area"],
            'nombreFunc' => $data["nombre_u"],
            'cedulaFunc' => $data["ci_u"],
            'firmaFunc' => $data["firma_u"],
            'nombreP' => $data["nombreC"],
            'cedulaP' => $data["cedula"],
            'telefonoP' => $data["telefono"],
            'firmaP' => $data["firma"]
        );
        
        while ($data=mysqli_fetch_assoc($res3)) {
                $procedente[$cont]= array(
                    "nombre_cientifico" => $data["nombre_cientifico"],
                    "nombre_comun"=> $data["nombre_comun"],
                    "edad" => $data["nom_edad"],
                    "sexo"=> $data["nom_sexo"],
                    "observacion"=> $data["observaciones_rec"]
                );
                $cont++;
            }
        
        $result = json_encode(array("success"=>true,"VerActa"=>$datauser,"procedente"=>$procedente));
        }
        else{
            $result = json_encode(array("success"=>false,"msg"=>"Error al traer la información"));
        }
        echo $result;
    }
    
    else if ($_GET['aksi'] == "list-arecepcion") {

    $res = $mysqli->query("SELECT AR.id_acta_recepcion, AR.num_acta_ar, AR.fecha_ar, AR.hora_ar, A.nom_tipo_atencion,
    MS.nom_mun,AR.nom_ldfe_barrio_ar, AR.nom_ldfecalle_ar, AR.num_ldfe_casa_ar, AR.nom_ldp_barrio_ar, AR.nom_ldp_calle_ar, 
    AR.nom_ldp_empresa_ar, AR.nom_ldp_area_ar, U.nombre_u, U.ci_u, U.firma_u, P.nombreC, P.cedula, P.telefono, P.firma
    FROM acta_recepcion as AR
    INNER JOIN tatencion as A
    ON AR.id_tipo_atencion=A.id_tipo_atencion
    INNER JOIN municipios as MS
    ON AR.id_ldfe_municipio=MS.id_municipio
    INNER JOIN usuarios as U
    ON AR.id_usuario=U.id_usuario
    INNER JOIN tblpersonas as P
    ON AR.id_persona=P.id_persona
    WHERE AR.estado_ar='1'");
    $cont = 0;
    $check = mysqli_num_rows($res);
    
    $res2 = $mysqli->query("SELECT MS.nom_mun FROM acta_recepcion as AR
    INNER JOIN municipios as MS
    ON AR.id_ldp_municipio=MS.id_municipio");
    
    $nombreMuS = mysqli_fetch_array($res2);
    $nombreMuS = $nombreMuS["nom_mun"];
        
    $check =mysqli_num_rows($res);
    
    if($check > 0){
        while ($data=mysqli_fetch_assoc($res)) {
                $datauser[$cont]= array(
                'id_acta_recepcion' => $data["id_acta_recepcion"],
                'num_acta_ar' => $data["num_acta_ar"],
                'fecha_ar' => $data["fecha_ar"],
                'hora_ar' => $data["hora_ar"],
                'nom_tipo_atencion' => $data["nom_tipo_atencion"],
                'municipioE' => $data["nom_mun"],
                'nom_ldfe_barrio_ar' => $data["nom_ldfe_barrio_ar"],
                'nom_ldfecalle_ar' => $data["nom_ldfecalle_ar"],
                'num_ldfe_casa_ar' => $data["num_ldfe_casa_ar"],
                'nom_ldp_barrio_ar' => $data["nom_ldp_barrio_ar"],
                'nom_ldp_calle_ar' => $data["nom_ldp_calle_ar"],
                'nom_ldp_empresa_ar' => $data["nom_ldp_empresa_ar"],
                'nom_ldp_area_ar' => $data["nom_ldp_area_ar"],
                'municipioS' => $nombreMuS,
                'nombre_u' => $data["nombre_u"],
                'ci_u' => $data["ci_u"],
                'firma_u' => $data["firma_u"],
                'nombreC' => $data["nombreC"],
                'cedula' => $data["cedula"],
                'telefono' => $data["telefono"],
                'firma' => $data["firma"]
                );
                $cont++;
            }
        
        $result = json_encode(array('success' => TRUE, "listArecepcion" => $datauser,"procedente"=>$procedente));
    } else {
        $result = json_encode(array('success' => false, 'msg' => 'No existen animales registrados'));
    }
    echo $result;
    } 
    else if($postjson['aksi'] == "lista-acta-animales"){
    $num_acta_ar = $postjson['num_acta_ar'];
    $cont = 0;
    $res3 = $mysqli->query("SELECT ani.nom_comun, e.nom_edad, sx.nom_sexo, proce.observaciones_rec 
    FROM acta_recepcion AS acta 
    INNER JOIN procedente_atencion as proce 
    ON acta.id_acta_recepcion=proce.id_acta_recepcion 
    INNER JOIN animal_silvestre as ani 
    ON ani.id_animal_silvestre=proce.id_animal_silvestre 
    INNER JOIN edad as e ON e.id_edad=ani.id_edad 
    INNER JOIN sexo as sx ON sx.id_sexo=ani.id_sexo 
    WHERE acta.num_acta_ar='$num_acta_ar'");
    $check2 =mysqli_num_rows($res3);
    if($check2 > 0){
        while ($data=mysqli_fetch_assoc($res3)) {
                $procedente[$cont]= array(
                    "nom_comun"=> $data["nom_comun"],
                    "nom_edad"=> $data["nom_edad"],
                    "nom_sexo"=> $data["nom_sexo"],
                    "observaciones_rec"=> $data["observaciones_rec"]
                );
                $cont++;
            }
            $result = json_encode(array('success' => TRUE, "procedente"=>$procedente));
    } else {
        $result = json_encode(array('success' => false, 'msg' => 'No existen animales registrados'));
    }
    echo $result;
        
    }
    else if($postjson['aksi'] == "update-ar"){
        $id_acta_recepcion = $postjson['id_acta_recepcion'];
        $num_acta_ar = $postjson['num_acta_ar'];
        $fecha_ar = $postjson['fecha_ar'];
        $hora_ar = $postjson['hora_ar'];
        $id_tipo_atencion = $postjson['id_tipo_atencion'];
        $id_ldfe_municipio  = $postjson['id_ldfe_municipio '];
        $nom_ldfe_barrio_ar = $postjson['nom_ldfe_barrio_ar'];
        $nom_ldfecalle_ar = $postjson['nom_ldfecalle_ar'];
        $num_ldfe_casa_ar = $postjson['num_ldfe_casa_ar'];
        $id_ldp_municipio  = $postjson['id_ldp_municipio '];
        $nom_ldp_barrio_ar = $postjson['nom_ldp_barrio_ar'];
        $nom_ldp_calle_ar = $postjson['nom_ldp_calle_ar'];
        $nom_ldp_empresa_ar = $postjson['nom_ldp_empresa_ar'];
        $nom_ldp_area_ar = $postjson['nom_ldp_area_ar'];
        $nom_funcionario_ar = $postjson['nom_funcionario_ar'];
        $firma_funcionario_ar = $postjson['firma_funcionario_ar'];
        $ci_funcionario_ar = $postjson['ci_funcionario_ar'];
        $nom_persona_ar = $postjson['nom_persona_ar'];
        $firma_persona_ar = $postjson['firma_persona_ar'];
        $telf_persona_ar = $postjson['telf_persona_ar'];
        $ci_persona_ar = $postjson['ci_persona_ar'];

        $res = $mysqli ->query("UPDATE acta_recepcion SET num_acta_ar='$num_acta_ar', fecha_ar='$fecha_ar', hora_ar = '$hora_ar', id_tipo_atencion = '$id_tipo_atencion',
        id_ldfe_municipio  = '$id_ldfe_municipio ', nom_ldfe_barrio_ar = '$nom_ldfe_barrio_ar', nom_ldfecalle_ar = '$nom_ldfecalle_ar', num_ldfe_casa_ar = '$num_ldfe_casa_ar', id_ldp_municipio  = '$id_ldp_municipio ',
        nom_ldp_barrio_ar = '$nom_ldp_barrio_ar', nom_ldp_calle_ar = '$nom_ldp_calle_ar', nom_ldp_empresa_ar = '$nom_ldp_empresa_ar', nom_ldp_area_ar = '$nom_ldp_area_ar', nom_funcionario_ar = '$nom_funcionario_ar',
        firma_funcionario_ar = '$firma_funcionario_ar',ci_funcionario_ar = '$ci_funcionario_ar', nom_persona_ar = '$nom_persona_ar', firma_persona_ar = '$firma_persona_ar', telf_persona_ar = '$telf_persona_ar',
        ci_persona_ar = '$ci_persona_ar' WHERE id_acta_recepcion=$id_acta_recepcion");
        if($res){
            $result = json_encode(array("success" => TRUE, "msg" => "Acta de recepción actualizada con exito"));
        }else{
            $result = json_encode(array("success" => FALSE, "msg" => "Hubo un error al actualizar el Acta de recepción"));
        }
        echo $result;

    }else if($postjson['aksi'] == "delete-ar"){
        $id_acta_recepcion = $postjson["id_acta_recepcion"];
        $res = $mysqli -> query("UPDATE acta_recepcion SET estado_ar = '0' WHERE id_acta_recepcion = $id_acta_recepcion");
        if ($res) {
            $result = json_encode(array("success" => TRUE, "msg" => "Acta de recepción deshabilitada"));
        } else {
            $result = json_encode(array("success" => false, 'msg' => 'Hubo un error al eliminar el acta de recepción'));
        }
        echo $result;
    }
    else if ($_GET['aksi'] == "list-eliminate") {

        $res = $mysqli->query("SELECT AR.id_acta_recepcion, AR.num_acta_ar, AR.fecha_ar, AR.hora_ar, A.nom_tipo_atencion,
        MS.nom_mun,AR.nom_ldfe_barrio_ar, AR.nom_ldfecalle_ar, AR.num_ldfe_casa_ar, AR.nom_ldp_barrio_ar, AR.nom_ldp_calle_ar, 
        AR.nom_ldp_empresa_ar, AR.nom_ldp_area_ar, U.nombre_u, U.ci_u, U.firma_u, P.nombreC, P.cedula, P.telefono, P.firma
        FROM acta_recepcion as AR
        INNER JOIN tatencion as A
        ON AR.id_tipo_atencion=A.id_tipo_atencion
        INNER JOIN municipios as MS
        ON AR.id_ldfe_municipio=MS.id_municipio
        INNER JOIN usuarios as U
        ON AR.id_usuario=U.id_usuario
        INNER JOIN tblpersonas as P
        ON AR.id_persona=P.id_persona
        WHERE AR.estado_ar='0'");
        $cont = 0;
        $check = mysqli_num_rows($res);
        
        $res2 = $mysqli->query("SELECT MS.nom_mun FROM acta_recepcion as AR
        INNER JOIN municipios as MS
        ON AR.id_ldp_municipio=MS.id_municipio");
        
        $nombreMuS = mysqli_fetch_array($res2);
        $nombreMuS = $nombreMuS["nom_mun"];
            
        $check =mysqli_num_rows($res);
        
        if($check > 0){
            while ($data=mysqli_fetch_assoc($res)) {
                    $datauser[$cont]= array(
                    'id_acta_recepcion' => $data["id_acta_recepcion"],
                    'num_acta_ar' => $data["num_acta_ar"],
                    'fecha_ar' => $data["fecha_ar"],
                    'hora_ar' => $data["hora_ar"],
                    'nom_tipo_atencion' => $data["nom_tipo_atencion"],
                    'municipioE' => $data["nom_mun"],
                    'nom_ldfe_barrio_ar' => $data["nom_ldfe_barrio_ar"],
                    'nom_ldfecalle_ar' => $data["nom_ldfecalle_ar"],
                    'num_ldfe_casa_ar' => $data["num_ldfe_casa_ar"],
                    'nom_ldp_barrio_ar' => $data["nom_ldp_barrio_ar"],
                    'nom_ldp_calle_ar' => $data["nom_ldp_calle_ar"],
                    'nom_ldp_empresa_ar' => $data["nom_ldp_empresa_ar"],
                    'nom_ldp_area_ar' => $data["nom_ldp_area_ar"],
                    'municipioS' => $nombreMuS,
                    'nombre_u' => $data["nombre_u"],
                    'ci_u' => $data["ci_u"],
                    'firma_u' => $data["firma_u"],
                    'nombreC' => $data["nombreC"],
                    'cedula' => $data["cedula"],
                    'telefono' => $data["telefono"],
                    'firma' => $data["firma"]
                    );
                    $cont++;
                }
            
            $result = json_encode(array('success' => TRUE, "listDisAr" => $datauser,"procedente"=>$procedente));
        } else {
            $result = json_encode(array('success' => false, 'msg' => 'No existen animales registrados'));
        }
        echo $result;
        } 
        else if($postjson['aksi'] == "lista-acta-animales"){
        $num_acta_ar = $postjson['num_acta_ar'];
        $cont = 0;
        $res3 = $mysqli->query("SELECT ani.nom_comun, e.nom_edad, sx.nom_sexo, proce.observaciones_rec 
        FROM acta_recepcion AS acta 
        INNER JOIN procedente_atencion as proce 
        ON acta.id_acta_recepcion=proce.id_acta_recepcion 
        INNER JOIN animal_silvestre as ani 
        ON ani.id_animal_silvestre=proce.id_animal_silvestre 
        INNER JOIN edad as e ON e.id_edad=ani.id_edad 
        INNER JOIN sexo as sx ON sx.id_sexo=ani.id_sexo 
        WHERE acta.num_acta_ar='$num_acta_ar'");
        $check2 =mysqli_num_rows($res3);
        if($check2 > 0){
            while ($data=mysqli_fetch_assoc($res3)) {
                    $procedente[$cont]= array(
                        "nom_comun"=> $data["nom_comun"],
                        "nom_edad"=> $data["nom_edad"],
                        "nom_sexo"=> $data["nom_sexo"],
                        "observaciones_rec"=> $data["observaciones_rec"]
                    );
                    $cont++;
                }
                $result = json_encode(array('success' => TRUE, "procedente"=>$procedente));
        } else {
            $result = json_encode(array('success' => false, 'msg' => 'No existen animales registrados'));
        }
        echo $result;
            
        }
    else if($postjson['aksi'] == "habilitar-ar"){
        $id_acta_recepcion = $postjson["id_acta_recepcion"];
        $res = $mysqli -> query("UPDATE acta_recepcion SET estado_ar = '1' WHERE id_acta_recepcion = $id_acta_recepcion");
        if ($res) {
            $result = json_encode(array("success" => TRUE, "msg" => "Acta de recepción habilitada"));
        } else {
            $result = json_encode(array("success" => false, 'msg' => 'Hubo un error al eliminar el acta de recepción'));
        }
        echo $result;
    }
?>