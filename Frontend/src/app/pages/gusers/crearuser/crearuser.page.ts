import { Component, OnInit } from '@angular/core';
import { ModalController, ToastController } from '@ionic/angular';
import { ConexionService } from '../../../servicios/conexion/conexion.service';
import { Cargos } from '../../../interfaces/cargo';

@Component({
  selector: 'app-crearuser',
  templateUrl: './crearuser.page.html',
  styleUrls: ['./crearuser.page.scss'],
})

export class CrearuserPage implements OnInit {
  users:any = [
    {
      name: "",
      email: "",
      ncargo: "",
      password: "",
      id_cargo: ""
    }
  ]
  cargos: Cargos[]
  constructor(private conexion  : ConexionService,
              private modalCtrl : ModalController,
              private toastCtrl : ToastController) { 
  }
  ngOnInit() { 
    this.ListCargo()
  }
  async mensaje (m: string){
    const t = await this.toastCtrl.create({
      message: m,
      duration: 3000
    })
    t.present();
  }
  closeModal(){
    this.modalCtrl.dismiss(null,'close');
  }

  ListCargo(){
    this.conexion.getdata("cargo.php/?aksi=list-cargo").subscribe((data:any)=>{
      this.cargos = data.listCargo
    })
  }

  RegisterUser(){
    const body = {
      name: this.users.name,
      email: this.users.email,
      password: this.users.password,
      id_cargo: this.users.id_cargo,
      aksi: "register-user"
    }
    this.conexion.postdata(body,"usuario.php").subscribe((data:any)=>{
      if (data.success) {
        this.mensaje(data.msg)
      } else {
        this.mensaje(data.msg)
      }
    })
  }
}
