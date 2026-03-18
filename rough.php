     <div id="customSearchWrapper" class="d-flex justify-content-between px-2 mt-2" style="position:sticky">

               
                    <div class="d-flex">

                        <input type="number" id="customPageInput" min="1" value="20">
                        <p class="entries-per-page-text mb-1">entries per page</p>

                    </div>
                  
                    <div class="d-flex">

                   <div class="d-flex">
                        <div class="search-icon-container"> <i class="fas fa-magnifying-glass text-secondary"></i>
                        </div>

                        <input type="text" style="" id="customSearchInput" placeholder="Search..." />
                    </div>
                   <button style="height: 2rem;" type="button" data-toggle="modal" data-target="#filterModal"
                class="btn btn-success btn-sm  bg-gradient ms-3">
                <i class="fa-solid fa-filter text-light"></i> Filter
            </button>
                   

                    <button style="height: 2rem;" type="button" data-toggle="modal" data-target="#add_project"
                class="btn btn-success btn-sm bg-gradient ml-2">
                <i class="fa-solid fa-plus text-light"></i> Add New Project
            </button>
                   </div>

                </div>