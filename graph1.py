import numpy as np
import matplotlib.pyplot as plt
Qo= 5*10**(-6)
omega=100*np.pi
T= 2*np.pi/omega
t= np.linspace(0,2*T,100)
I= Qo*omega*np.cos(omega*t)
plt.plot(t,I)
plt.grid()
plt.show()