document.getElementById("addCustomerManually").onclick = function() {
    document.getElementById("customerSidebar").style.width = "691px";
    document.getElementById("customerSidebar").style.padding = "0px 14px";
    // document.getElementById("overlayCustomer").style.display = "block";
};

document.getElementById("closeCustomerSidebar").onclick = function() {
    document.getElementById("customerSidebar").style.width = "0";
    document.getElementById("customerSidebar").style.padding = "0";
    // document.getElementById("overlayCustomer").style.display = "none";
};

function closeCustomerSidebar() {
    document.getElementById("customerSidebar").style.width = "0";
    // document.getElementById("overlayCustomer").style.display = "none";
}



