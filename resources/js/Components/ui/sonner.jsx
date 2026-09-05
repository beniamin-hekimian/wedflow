import { Toaster as Sonner } from "sonner";

function Toaster({
  position = "top-right",
  richColors = true,
  closeButton = true,
  ...props
}) {
  return (
    <Sonner
      position={position}
      richColors={richColors}
      closeButton={closeButton}
      {...props}
    />
  );
}

export { Toaster };