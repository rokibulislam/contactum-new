<template>
    <div class="module">
      <div class="contactum-modules">

<div class="clearfix">
    <div class="pull-right activate-deactivate-all">
        <a href="#" @click.prevent="toggleModule('all','activate')">Activate</a> |
        <a href="#" @click.prevent="toggleModule('all','deactivate')">Deactivate</a> All
    </div>

    <h1 class="pull-left"> Modules </h1>
</div>

<div class="wp-list-table widefat contactum-modules">

    <div class="plugin-card" v-for="(module, key) in modules.all">
        <div class="plugin-card-top">

            <div class="name column-name">
                <h3>
                    <span class="plugin-name">{{ module.name }}</span>
                    <img class="plugin-icon" :src="module.thumbnail" :alt="module.name" />
                </h3>
            </div>

            <div class="action-links">
                <ul class="plugin-action-buttons">
                    <li>
                        <span :class="['contactum-toggle-switch', 'big', isActive(key) ? 'checked' : '']" v-on:click="toggleModule(key)"></span>
                        <el-switch
                            @change="toggleModule(key)" 
                            active-value="activate" 
                            inactive-value="deactivate" 
                            v-model="module.name"
                        />
                    </li>
                </ul>
            </div>

            <div class="desc column-description">
                <p>
                    {{ module.description }}
                </p>
            </div>
        </div>
    </div>

</div>

</div>

    </div>
  </template>
  
  <script>
  export default {
    name: "Module",
    data: function() {
        return {
            requesting: false,
            loading: false,
            modules: {
                all: {},
                active: []
            },
        };
    },

    mounted() {
      this.fetchModules();
    },
    created: function() {

    },

    methods: {

        isActive: function( module ) {
            return this.modules.active.includes(module);
        },

        activateModule: function(module) {
            if ( !this.isActive(module) ) {
                this.modules.active.push(module);
            }
        },

        deactivateModule: function(module) {

            if ( this.isActive(module) ) {
                this.modules.active.splice( this.modules.active.indexOf(module), 1 );
            }
        },

        fetchModules: function() {
            var self = this;

            self.loading = true;


            let data = {
              'action': 'contactum_get_modules',
              _wpnonce: contactum.nonce
            };

            jQuery.ajax({
              url: contactum.ajaxurl,
              type: 'POST',
              data: data,
              success: function(response) {
                self.modules = response.data;
              },
              complete: function() {
                self.loading = false;
              }
            });
        },

        toggleModule: function(module, state) {
            var self = this;
            
            if ( ! state ) {
                state = this.isActive(module) ? 'deactivate' : 'activate';
            }

            // if we are already making a call
            if (self.requesting) {
                return;
            }

            self.requesting = true;
            self.loading    = true;


          let data = {
            action: 'contactum_toggle_modules',
            _wpnonce: contactum.nonce,
            type: state,
            module: module,
          };


          jQuery.ajax({
            url: contactum.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
              self.modules = response;
            },
            complete: function() {

              if ( state === 'activate' ) {
                self.activateModule(module);
              } else {
                self.deactivateModule(module);
              }

              toastr.options.timeOut = 1000;
              toastr.success( response );

              // window.location.reload();
              self.requesting = false;
              self.loading    = false;
            }
          });

            wp.ajax.send( 'contactum_toggle_modules', {
                data: {
                    type: state,
                    module: module,
                    _wpnonce: contactum.nonce
                },

                success: function(response) {


                },

                complete: function() {
                    self.requesting = false;
                    self.loading    = false;
                }
            });
        }
    }
};
  </script>

  <style>

.contactum-toggle-switch {
  cursor: pointer;
  text-indent: -9999px;
  width: 25px;
  height: 15px;
  background: #ccc;
  display: block;
  border-radius: 100px;
  position: relative;
}
.contactum-toggle-switch:after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 12px;
  height: 12px;
  background: #fff;
  border-radius: 50%;
  transition: 0.3s;
}
.contactum-toggle-switch.checked {
  background: #0085ba;
}
.contactum-toggle-switch.checked:after {
  left: calc(90%);
  transform: translateX(-100%);
}
.contactum-toggle-switch.big {
  width: 35px;
  height: 20px;
}
.contactum-toggle-switch.big:after {
  top: 3px;
  left: 3px;
  width: 15px;
  height: 15px;
}
.contactum-toggle-switch.big.checked:after {
  left: calc(95%);
}
</style>