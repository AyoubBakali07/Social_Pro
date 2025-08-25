declare module '@/toast' {
  import { ToastT } from 'sonner/dist/types'
  
  export const toast: {
    (message: string, options?: ToastT): void;
    success: (message: string, options?: ToastT) => void;
    error: (message: string, options?: ToastT) => void;
    warning: (message: string, options?: ToastT) => void;
    info: (message: string, options?: ToastT) => void;
    loading: (message: string, options?: ToastT) => void;
    promise: <T>(
      promise: Promise<T> | (() => Promise<T>),
      msgs: {
        loading: string;
        success: string | ((data: T) => string);
        error: string | ((error: any) => string);
      },
      opts?: ToastT
    ) => Promise<T>;
  }
}
