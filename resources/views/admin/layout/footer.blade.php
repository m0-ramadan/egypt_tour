@include('admin.i18n.locale')
   <footer class="content-footer footer bg-footer-theme">
       <div class="container-xxl">
           <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
               <div>


                   <a href="{{ env('APP_URL') }}" target="_blank"
                       class="footer-link text-primary fw-medium">{{ env('APP_NAME') }}</a>
                   Made with by

               </div>

           </div>
       </div>
   </footer>
