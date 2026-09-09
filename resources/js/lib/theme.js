import { useSyncExternalStore } from "react";

const THEME_KEY = "theme";

const listeners = new Set();

function notify() {
  listeners.forEach((listener) => listener());
}

function subscribe(listener) {
  listeners.add(listener);
  return () => listeners.delete(listener);
}

function currentTheme() {
  return document.documentElement.classList.contains("dark") ? "dark" : "light";
}

function setTheme(theme) {
  document.documentElement.classList.toggle("dark", theme === "dark");
  localStorage.setItem(THEME_KEY, theme);
  notify();
}

export function toggleTheme() {
  setTheme(currentTheme() === "dark" ? "light" : "dark");
}

export function useTheme() {
  useSyncExternalStore(subscribe, currentTheme);
  return { theme: currentTheme(), toggle: toggleTheme };
}