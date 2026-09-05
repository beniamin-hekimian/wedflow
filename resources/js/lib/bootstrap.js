import axios from "axios";
import { router } from "@inertiajs/react";
import { toast } from "sonner";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

router.on("success", (event) => {
    const { flash } = event.page.props;

    if (flash?.success) {
        toast.success(flash.success);
    }

    if (flash?.error) {
        toast.error(flash.error);
    }
});