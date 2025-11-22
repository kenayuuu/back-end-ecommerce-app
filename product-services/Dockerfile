#gunakan imag nod e versi 18 sebagai base image
FROM node:18-alpine

#set working directory di dalam container
WORKDIR /app

#copy file package.json dan package-lock.json ke working directory
COPY package.json . /app/

#install dependencies aplikasi
RUN npm install

#copy semua file dari direktori saat ini ke working directory di dalam container
COPY . .

#expose port 3000 untuk aplikasi
EXPOSE 3000

#jalankan perintah untuk memulai aplikasi
CMD ["npm", "start"]