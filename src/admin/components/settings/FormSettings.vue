<template>
  <div class="contactum_cad"> </div>
</template>

 <script>

 export default {
   name: 'FormSettings',
   data() {
     return {

     }
   },
   computed: {
     globalintegrations: function () {
       return this.$store.getters.globalintegrations;
     },
   },
   mounted() {
     jQuery.post(contactum.ajaxurl, {
           action: 'contactum_get_admin_integrations',
           _ajax_nonce: contactum.nonce
         },
         (response, textStatus, xhr) => {
           if (response.success) {
            // this.$store.dispatch("setglobalintegrations", response.data);
           } else {
             console.error('Error:', response || 'Unknown error');
           }
     });
   }
 }

 </script>
