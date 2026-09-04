# Agent Instructions

## Frontend / UI Conventions

- **Use shadcn/ui components as much as possible** instead of the built-in Laravel/Breeze components, on every new page file created from now on.
- **Do not override the same styles of shadcn/ui components** — avoid adding too many custom class names that duplicate or conflict with the shadcn component's own styling. Keep custom class names minimal.
- **Do not use the `asChild` prop** on buttons. The project uses `@base-ui/react` buttons which rely on the `render` prop, not `asChild`. Instead, **wrap the button inside the `Link` component**:
  ```jsx
  <Link href={route('...')}>
      <Button>Label</Button>
  </Link>
  ```

## Indentation / Tabs

- **Use 2-space indentation for ALL frontend files under `resources/`** (e.g. `.jsx`, `.js`, `.css` files).
- **Keep 4-space indentation for all backend PHP files** (`app/`, `routes/`, `database/`, `tests/`, etc.).
- PHP files must never be changed to 2-space indentation, and frontend files must never use 4-space indentation.
